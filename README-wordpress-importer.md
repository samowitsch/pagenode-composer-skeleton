# Wordpress importer

The importer generates Markdown files of Wordpress posts for [Pagenode](https://pagenode.org/).

## Requirements

The complete Wordpress instance with database dump inside the ```import``` folder.  
For example ```import/www.wordpress-instance-to-import.de/*```

## Usage

I assume that you also use [DDEV](https://ddev.readthedocs.io/en/stable/) as development environment and have it up and running.

### import database dump to DDEV

```ddev import-db file=/import/www.wordpress-instance-to-import.de/name_of_dumpfile.sql.gz```

> ⚠️  change path and dump filename for your case

### show DDEV settings

Use ```ddev describe``` to show current database settings. Change [/config/import-wordpress-config.yaml](/config/import-wordpress-config.yaml) to your needs.

### second Import with public import-wordpress.php

Execute importer in public folder ```https://pagenode-composer-skeleton.ddev.site:8443/import-wordpress.php```

> ⚠️  Change url to your local DDEV configuration. See: [/.ddev/config.yaml](/.ddev/config.yaml)  
> ⚠️  Check/change configuration. See: [/config/import-wordpress-config.yaml](/config/import-wordpress-config.yaml)

### or Import with command

```shell
./bin/console import:wordpress
```

> ⚠️  Untested but should work.  
> ⚠️  Check/change configuration. See: [/config/import-wordpress-config.yaml](/config/import-wordpress-config.yaml)

## Dev Notes [TO DELETE FINALLY!]

```sql
SELECT * FROM `wp_posts` WHERE post_title like '%fanatec%' AND post_type = 'post'

SELECT * FROM `wp_postmeta` where post_id = 2199

SELECT * FROM `wp_posts` 
WHERE post_status = "publish" and post_type = "post" 
ORDER BY `wp_posts`.`ID` DESC


SELECT * FROM `wp_posts`
WHERE post_type = "attachment"
AND post_parent > 0  
ORDER BY `wp_posts`.`post_parent`  DESC

SELECT t1.*, t2.* 
FROM `wp_posts` t1
LEFT JOIN `wp_posts` t2
  ON t2.post_parent = t1.ID
WHERE 
   	(t1.post_type = 'post' AND t1.post_status="publish")
	AND (t2.post_type = 'attachment' AND t2.post_parent > 0)
ORDER BY `t1`.`post_date`  DESC

```

### search for file in wp project folder

```shell
find ./ -type f -iname "*621x372*"
find ./ -type f -iname "*typo3-logo*"

find ./ -type f -iname "*DSC5675-672x372.jpg*"
DSC5675-672x372.jpg


csg@csg-deskmini-300:~/html/mm/import/www.motions-media.de$ find ./ -type f -iname "*mini-2-672x372*"
./wp-content/uploads/2015/04/ubuntu-mac-mini-2-672x372.png

```

### Queries for category and keywords

```sql
SELECT * FROM `wp_term_relationships` where object_id = 2199

SELECT * FROM `wp_term_taxonomy` where term_taxonomy_id in (207, 208, 209, 210, 211, 212, 213)

SELECT * FROM `wp_terms` where term_id in (207, 208, 209, 210, 211, 212, 213)
```

```sql
#Category
SELECT * FROM wp_term_relationships 
INNER JOIN wp_term_taxonomy ON (wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id AND wp_term_taxonomy.taxonomy='category')
INNER JOIN wp_terms ON (wp_terms.term_id=wp_term_taxonomy.term_id )
WHERE object_id=2199


SELECT * FROM wp_term_relationships 
INNER JOIN wp_term_taxonomy ON (wp_term_taxonomy.term_taxonomy_id=wp_term_relationships.term_taxonomy_id AND wp_term_taxonomy.taxonomy='post_tag')
INNER JOIN wp_terms ON (wp_terms.term_id=wp_term_taxonomy.term_id )
WHERE object_id=2199
```