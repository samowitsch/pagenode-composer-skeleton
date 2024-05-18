<?php


#
# Pagenode
# http://pagenode.org
#
# (c) Dominic Szablewski
# https://phoboslab.org
#

define('PN_VERSION', '0.1');

if (!defined('PN_DATE_FORMAT'))
    define('PN_DATE_FORMAT', 'M d, Y - H:i:s');

if (!defined('PN_DATE_FORMAT_LOCALIZED'))
    /**
     * @see https://unicode-org.github.io/icu/userguide/format_parse/datetime/
     */
    define('PN_DATE_FORMAT_LOCALIZED', 'EEEE, dd.MM.yyyy');

if (!defined('PN_SYNTAX_HIGHLIGHT_LANGS'))
    define('PN_SYNTAX_HIGHLIGHT_LANGS', 'php|js|sql|c');

if (!defined('PN_CACHE_INDEX_PATH'))
    define('PN_CACHE_INDEX_PATH', null);

if (!defined('PN_CACHE_USE_INDICATOR_FILE'))
    define('PN_CACHE_USE_INDICATOR_FILE', false);

if (!defined('PN_CACHE_INDICATOR_FILE'))
    define('PN_CACHE_INDICATOR_FILE', '.git/FETCH_HEAD');

if (!defined('PN_JSON_API_FULL_DEBUG_INFO'))
    define('PN_JSON_API_FULL_DEBUG_INFO', false);

if (defined('PN_TIMEZONE'))
    date_default_timezone_set(PN_TIMEZONE);
else if (!date_default_timezone_get())
    date_default_timezone_set('UTC');


$PN_TimeStart = microtime(true);
header('Content-type: text/html; charset=UTF-8');
define('PN_ABS',
    rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/');


// -----------------------------------------------------------------------------
// Selector Class - provides a query interface for Nodes on the filesystem

class PN_Selector
{
    public static $DebugInfo = [];

    protected $path = null, $indexPath = null;
    protected static $IndexCache = [];
    protected static $FoundNodes = 0;

    const SORT_DESC = 'desc';
    const SORT_ASC = 'asc';

    public function __construct($path)
    {
        $this->path = realpath('./' . $path . '/');
        if (!$this->path || strstr($path, '..') !== false) {
            header("HTTP/1.1 500 Internal Error");
            echo 'select("' . htmlSpecialChars($path) . '") does not exist.';
            exit();
        }
        $this->indexPath =
            (PN_CACHE_INDEX_PATH ?? sys_get_temp_dir()) .
            '/pagenode-index-' . md5($this->path) . '.json';
    }

    protected function rebuildIndex()
    {
        $index = [];
        foreach (glob($this->path . '/*.md') as $path) {
            $meta = $this->loadMetaFromFile($path);
            if ($meta['active'] !== false) {
                $keyword = pathInfo($path, PATHINFO_FILENAME);
                $index[$keyword] = $meta;
            }
        }

        if (empty($index)) {
            return $index;
        }

        uasort($index, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        $jsonOpts = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $json = json_encode($index, $jsonOpts);
        file_put_contents($this->indexPath, $json);

        return $index;
    }

    protected function indexIsCurrent()
    {
        if (!file_exists($this->indexPath)) {
            return false;
        }

        $indexTime = filemtime($this->indexPath);
        if (
            PN_CACHE_USE_INDICATOR_FILE &&
            file_exists(PN_CACHE_INDICATOR_FILE)
        ) {
            return $indexTime > filemtime(PN_CACHE_INDICATOR_FILE);
        }

        $lastFileTime = 0;
        foreach (glob($this->path . '/*.md') as $f) {
            $lastFileTime = max($lastFileTime, filemtime($f));
        }
        return $indexTime > $lastFileTime;
    }

    protected function getIndex()
    {
        $timeStart = microtime(true);
        $didRebuild = false;

        if (!isset(self::$IndexCache[$this->path])) {
            if ($this->indexIsCurrent()) {
                $json = file_get_contents($this->indexPath);
                self::$IndexCache[$this->path] = json_decode($json, true);
            } else {
                self::$IndexCache[$this->path] = $this->rebuildIndex();
                $didRebuild = true;
            }

            self::$DebugInfo[] = [
                'action' => 'loadIndex',
                'path' => $this->path,
                'indexPath' => $this->indexPath,
                'ms' => round((microtime(true) - $timeStart) * 1000, 3),
                'didRebuild' => (int)$didRebuild,
                'cacheMethod' => PN_CACHE_USE_INDICATOR_FILE
                    ? 'INDICATOR_FILE'
                    : 'NODE_LAST_MODIFIED'
            ];
        }

        return self::$IndexCache[$this->path] ?? [];
    }

    protected function loadMetaFromFile($path)
    {
        $meta = [];
        $file = file_get_contents($path);
        if (preg_match('/(.*?)^---\s*$/ms', $file, $metaSection)) {
            preg_match_all('/^(\w+):(.*)$/m', $metaSection[1], $metaAttribs);
            foreach ($metaAttribs[1] as $i => $key) {
                $meta[$key] = trim($metaAttribs[2][$i]);
            }
        }

        $meta['tags'] = !empty($meta['tags'])
            ? array_map('trim', explode(',', $meta['tags']))
            : [];


        if (
            !empty($meta['date']) &&
            preg_match(
                '/(\d{4})[\.\-](\d{2})[\.\-](\d{2})( (\d{2}):(\d{2}))?/',
                $meta['date'],
                $dateMatch
            )
        ) {
            $y = $dateMatch[1];
            $m = $dateMatch[2];
            $d = $dateMatch[3];
            $h = !empty($dateMatch[5]) ? $dateMatch[5] : 0;
            $i = !empty($dateMatch[6]) ? $dateMatch[6] : 0;
            $meta['date'] = mktime($h, $i, 0, $m, $d, $y);
        } else {
            $meta['date'] = filemtime($path);
        }

        $meta['active'] = empty($meta['active']) || $meta['active'] !== 'false';

        return $meta;
    }


    public static function FoundNodes()
    {
        return self::$FoundNodes;
    }

    public function one($params = [], $raw = false)
    {
        $nodes = $this->query('date', self::SORT_DESC, 1, $params, $raw);
        return !empty($nodes) ? $nodes[0] : null;
    }

    public function newest($count = 0, $params = [], $raw = false)
    {
        return $this->query('date', self::SORT_DESC, $count, $params, $raw);
    }

    public function oldest($count = 0, $params = [], $raw = false)
    {
        return $this->query('date', self::SORT_ASC, $count, $params, $raw);
    }

    public function query($sort, $order, $count, $params, $raw = false)
    {
        if (!$this->path) {
            return [];
        }

        $index = $this->getIndex();

        $timeStart = microtime(true);
        $scannedNodes = count($index);

        // Filter by keyword. Since keywords are unique, we can simply index
        // by it, returning only one node.

        if (!empty($params['keyword'])) {
            $index = !empty($index[$params['keyword']])
                ? [$params['keyword'] => $index[$params['keyword']]]
                : [];
        }


        // Filter by date. Allow to become more granual by specifying either
        // just year, year & month or year & month & day.

        if (!empty($params['date'])) {
            $y = $params['date'][0] ?? $params['date'];
            $m = $params['date'][1] ?? null;
            $d = $params['date'][2] ?? null;
            if (preg_match('/(\d{4}).(\d{2}).(\d{2})/', $y, $match)) {
                $y = $match[1];
                $m = $match[2];
                $d = $match[3];
            }
            $start = mktime(0, 0, 0, ($m ? $m : 1), ($d ? $d : 1), $y);
            $end = mktime(23, 59, 59, ($m ? $m : 12), ($d ? $d : 31), $y);

            $index = array_filter($index, function ($n) use ($start, $end) {
                return $n['date'] >= $start && $n['date'] <= $end;
            });
        }


        // Filter by tags. Only return nodes that match all given tags.

        if (!empty($params['tags'])) {
            $tags = !is_array($params['tags'])
                ? array_map('trim', explode(',', $params['tags']))
                : $params['tags'];
            $index = array_filter($index, function ($n) use ($tags) {
                return !array_udiff($tags, $n['tags'], 'strcasecmp');
            });
        }


        // Filter by arbitrary properties

        if (!empty($params['meta'])) {
            $meta = $params['meta'];
            $index = array_filter($index, function ($n) use ($meta) {
                foreach ($meta as $key => $value) {
                    if (!isset($n[$key]) || $n[$key] !== $value) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Filter using a custom filter function

        if (!empty($params['filter']) && is_callable($params['filter'])) {
            $index = array_filter($index, $params['filter']);
        }


        // Sort by any property

        if ($sort === 'date' && $order === self::SORT_DESC) {
            // Nothing to do here; index is sorted by date, desc by default
        } else {
            if ($order === self::SORT_ASC) {
                uasort($index, function ($a, $b) use ($sort) {
                    return ($a[$sort] ?? INF) <=> ($b[$sort] ?? INF);
                });
            } else {
                uasort($index, function ($a, $b) use ($sort) {
                    return ($b[$sort] ?? 0) <=> ($a[$sort] ?? 0);
                });
            }
        }


        // Keep track of the total nodes found with the given filter params

        self::$FoundNodes = count($index);


        // Slice and Paginate

        if ($count) {
            $offset = ($params['page'] ?? 0) * $count;
            $index = array_slice($index, $offset, $count, true);
        }


        // Create Nodes

        $nodes = [];
        foreach ($index as $keyword => $meta) {
            $nodePath = $this->path . '/' . $keyword . '.md';
            $nodes[] = new PN_Node($nodePath, $keyword, $meta, $raw);
        }

        self::$DebugInfo[] = [
            'action' => 'query',
            'path' => $this->path,
            'ms' => round((microtime(true) - $timeStart) * 1000, 3),
            'scanned' => $scannedNodes,
            'returned' => count($nodes),
            'params' => $params
        ];

        return $nodes;
    }
}


// -----------------------------------------------------------------------------
// DateTime class - a simple wrapper for timestamps

class PN_DateTime
{
    protected $timestamp;

    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function format($format = PN_DATE_FORMAT)
    {
        return htmlSpecialChars(date($format, $this->timestamp));
    }

    /**
     * https://unicode-org.github.io/icu/userguide/format_parse/datetime/
     *
     * @param string $format
     * @param string $lang
     * @return string
     */
    public function formatLocalized(string $format = PN_DATE_FORMAT_LOCALIZED, string $lang = 'de_DE')
    {
        $fmt = new IntlDateFormatter(
            $lang,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Europe/Berlin',
            IntlDateFormatter::GREGORIAN,
            $format
        );

        return htmlSpecialChars(datefmt_format($fmt, $this->timestamp));
    }

    public function __toString()
    {
        return $this->format();
    }
}


// -----------------------------------------------------------------------------
// Node Class - each Node instance represents a single file

class PN_Node
{
    public static $DebugOpenedNodes = [];

    public $keyword, $tags = [], $date;
    protected $path, $meta = [], $body = null, $raw = false;

    public function __construct($path, $keyword, $meta, $raw = false)
    {
        $this->raw = $raw;
        $this->path = $path;
        $this->keyword = pathInfo($path, PATHINFO_FILENAME);
        $this->date = $raw ? $meta['date'] : new PN_DateTime($meta['date']);
        $this->meta = $meta;

        if (!$raw) {
            foreach ($meta['tags'] as $t) {
                $this->tags[] = htmlSpecialChars($t);
            }
        } else {
            $this->tags = $meta['tags'];
        }
    }

    protected function loadBody()
    {
        self::$DebugOpenedNodes[] = $this->path;
        $file = file_get_contents($this->path);

        $markdown = (preg_match('/^---\s*$(.*)/ms', $file, $m))
            ? $m[1]
            : $file;

        if ($this->raw) {
            return $markdown;
        } else {
            return !empty(PN_SYNTAX_HIGHLIGHT_LANGS)
                ? PN_ParsedownSyntaxHighlight::instance()->text($markdown)
                : Parsedown::instance()->text($markdown);
        }
    }

    public function hasTag($tag)
    {
        return in_array($tag, $this->meta['tags']);
    }

    public function __get($name)
    {
        if ($name === 'body') {
            if (!$this->body) {
                $this->body = $this->loadBody();
            }
            return $this->body;
        } else if (isset($this->meta[$name])) {
            return $this->raw
                ? $this->meta[$name]
                : htmlSpecialChars($this->meta[$name]);
        }

        return null;
    }
}


// -----------------------------------------------------------------------------
// Router Class - handles routes and dispatch

class PN_Router
{
    public static $Routes = [];

    public static function AddRoute($path, $resolver)
    {
        $r = str_replace('/', '\\/', $path);
        $r = str_replace('*', '.*?', $r);
        $r = preg_replace('/{(\w+)}/', '(?<$1>[^\\/]+?)', $r);
        $regexp = '/^' . $r . '$/';

        self::$Routes[$path] = [
            'regexp' => $regexp,
            'resolver' => $resolver
        ];
    }

    public static function Dispatch($request)
    {
        foreach (self::$Routes as $path => $r) {
            if (preg_match($r['regexp'], $request, $m)) {
                $found = self::Resolve($r['resolver'], $m);
                return ($found && $path !== '/*');
            }
        }
        return self::ErrorNotFound();
    }

    public static function Resolve($resolver, $regexpMatch, $recurse = true)
    {
        $params = array_filter($regexpMatch, function ($key) {
            return !is_int($key);
        }, ARRAY_FILTER_USE_KEY);

        if (call_user_func_array($resolver, $params) !== false) {
            return true;
        };

        return self::ErrorNotFound($recurse);
    }

    public static function ErrorNotFound($recurse = true)
    {
        if ($recurse && !empty(self::$Routes['/*'])) {
            self::Resolve(self::$Routes['/*']['resolver'], [], false);
        } else {
            header("HTTP/1.1 404 Not Found");
            echo "Not Found";
        }
        return false;
    }
}


// -----------------------------------------------------------------------------
// Generic Syntax Highlighting extension for Parsedown

class PN_ParsedownSyntaxHighlight extends Parsedown
{
    public static function SyntaxHighlight($s)
    {
        $s = htmlSpecialChars($s) . "\n";
        $s = str_replace('\\\\', '\\\\<e>', $s); // break escaped backslashes

        $tokens = [];
        $transforms = [
            // Insert helpers to find regexps
            '/
				([\[({=:+,]\s*)
					\/
				(?![\/\*])
			/x'
            => '$1<h>/',

            // Extract Comments, Strings & Regexps, insert them into $tokens
            // and return the index
            '/(
				\/\*.*?\*\/|
				\/\/.*?\n|
				\#.*?\n|
				--.*?\n|
				(?<!\\\)&quot;.*?(?<!\\\)&quot;|
				(?<!\\\)\'(.*?)(?<!\\\)\'|
				(?<!\\\)<h>\/.+?(?<!\\\)\/\w*
			)/sx'
            => function ($m) use (&$tokens) {
                $id = '<r' . count($tokens) . '>';
                $block = $m[1];

                if ($block[0] === '&' || $block[0] === "'") {
                    $type = 'string';
                } else if ($block[0] === '<') {
                    $type = 'regexp';
                } else {
                    $type = 'comment';
                }
                $tokens[$id] = '<span class="' . $type . '">' . $block . '</span>';
                return $id;
            },

            // Punctuation
            '/((
				&\w+;|
				[-\/+*=?:.,;()\[\]{}|%^!]
			)+)/x'
            => '<span class="punct">$1</span>',

            // Numbers (also look for Hex encoding)
            '/(?<!\w)(
				0x[\da-f]+|
				\d+
			)(?!\w)/ix'
            => '<span class="number">$1</span>',

            // Keywords
            '/(?<!\w|\$)(
				and|or|xor|not|for|do|while|foreach|as|endfor|endwhile|break|
				endforeach|continue|return|die|exit|if|then|else|elsif|elseif|
				endif|new|delete|try|throw|catch|finally|switch|case|default|
				goto|class|function|extends|this|self|parent|public|private|
				protected|published|friend|virtual|
				string|array|object|resource|var|let|bool|boolean|int|integer|
				float|double|real|char|short|long|const|static|global|
				enum|struct|typedef|signed|unsigned|union|extern|true|false|
				null|void
			)(?!\w|=")/ix'
            => '<span class="keyword">$1</span>',

            // PHP-Style Vars: $var
            '/(?<!\w)(
				\$(\-&gt;|\w)+
			)(?!\w)/ix'
            => '<span class="var">$1</span>',

            // Make the bold assumption that an all uppercase word has a
            // special meaning
            '/(?<!\w|\$|>)(
				[A-Z_][A-Z_0-9]+
			)(?!\w)/x'
            => '<span class="def">$1</span>'
        ];

        foreach ($transforms as $search => $replace) {
            $s = is_string($replace)
                ? preg_replace($search, $replace, $s)
                : preg_replace_callback($search, $replace, $s);
        }

        // Paste the comments and strings back in again
        $s = strtr($s, $tokens);

        // Delete the escaped backslash breaker and replace tabs with 4 spaces
        $s = str_replace(['<e>', '<h>', "\t"], ['', '', '    '], $s);

        return trim($s, "\n\r");
    }

    protected function blockFencedCodeComplete($Block)
    {
        $class = $Block['element']['element']['attributes']['class'] ?? null;
        $re = '/^language-(' . PN_SYNTAX_HIGHLIGHT_LANGS . ')$/';
        if (empty($class) || !preg_match($re, $class)) {
            return $Block;
        }

        $text = $Block['element']['element']['text'];
        unset($Block['element']['element']['text']);
        $Block['element']['element']['rawHtml'] = self::SyntaxHighlight($text);
        $Block['element']['element']['allowRawHtmlInSafeMode'] = true;
        return $Block;
    }
}


// -----------------------------------------------------------------------------
// mb_strlen polyfill for Parsedown when mbstring extension is not installed

if (!function_exists('mb_strlen')) {
    function mb_strlen($s)
    {
        $byteLength = strlen($s);
        for ($q = 0, $i = 0; $i < $byteLength; $i++, $q++) {
            $c = ord($s[$i]);
            if ($c >= 0 && $c <= 127) {
                $i += 0;
            } else if (($c & 0xE0) == 0xC0) {
                $i += 1;
            } else if (($c & 0xF0) == 0xE0) {
                $i += 2;
            } else if (($c & 0xF8) == 0xF0) {
                $i += 3;
            } else return $byteLength; //invalid utf8
        }
        return $q;
    }
}


// -----------------------------------------------------------------------------
// PAGENODE Public API

function select($path = '')
{
    return new PN_Selector($path);
}

function foundNodes()
{
    return PN_Selector::FoundNodes();
}

function route($path, $resolver = null)
{
    PN_Router::AddRoute($path, $resolver);
}

function reroute($source, $target)
{
    route($source, function () use ($target) {
        $args = func_get_args();
        $target = preg_replace_callback(
            '/{(\w+)}/',
            function ($m) use ($args) {
                return $args[$m[1] - 1] ?? '';
            },
            $target
        );
        dispatch($target);
    });
}

function redirect($path = '/', $params = [])
{
    $query = !empty($params)
        ? '?' . http_build_query($params)
        : '';
    header('Location: ' . $path . $query);
    exit();
}

function dispatch($request = null)
{
    if ($request === null) {
        $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $request = '/' . substr($request, strlen(PN_ABS));
    }

    $found = PN_Router::Dispatch($request);
}

function getDebugInfo()
{
    global $PN_TimeStart;
    return [
        'totalRuntime' => (microtime(true) - $PN_TimeStart) * 1000,
        'selctorInfo' => PN_Selector::$DebugInfo,
        'openedNodes' => PN_Node::$DebugOpenedNodes
    ];
}

function printDebugInfo()
{
    echo "<pre>\n" . htmlSpecialChars(print_r(getDebugInfo(), true)) . "</pre>";
}


// -----------------------------------------------------------------------------
// PAGENODE JSON Route, disabled by default

if (defined('PN_JSON_API_PATH')) {
    route(PN_JSON_API_PATH, function () {
        $nodes = select($_GET['path'] ?? '')->query(
            $_GET['sort'] ?? 'date',
            $_GET['order'] ?? 'desc',
            $_GET['count'] ?? 0,
            [
                'keyword' => $_GET['keyword'] ?? null,
                'date' => $_GET['date'] ?? null,
                'tags' => $_GET['tags'] ?? null,
                'meta' => $_GET['meta'] ?? null,
                'page' => $_GET['page'] ?? null
            ],
            true
        );

        $fields = !empty($_GET['fields'])
            ? array_map('trim', explode(',', $_GET['fields']))
            : ['keyword'];

        header('Content-type: application/json; charset=UTF-8');
        echo json_encode([
            'nodes' => array_map(function ($n) use ($fields) {
                $ret = [];
                foreach ($fields as $f) {
                    $ret[$f] = $n->$f;
                }
                return $ret;
            }, $nodes),
            'info' => PN_JSON_API_FULL_DEBUG_INFO
                ? getDebugInfo()
                : ['totalRuntime' => getDebugInfo()['totalRuntime']]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    });
}
