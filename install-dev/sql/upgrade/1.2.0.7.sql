SET NAMES 'utf8';

/* ##################################### */
/* 				STRUCTURE			 		 */
/* ##################################### */

ALTER IGNORE TABLE `PREFIX_blocklink_lang` ADD PRIMARY KEY (`id_link`, `id_lang`);
ALTER IGNORE TABLE `PREFIX_product_comment_grade` ADD PRIMARY KEY (`id_product_comment`, `id_product_comment_criterion`);
ALTER IGNORE TABLE `PREFIX_product_comment_criterion` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_product_comment_criterion`, `id_lang`);
ALTER IGNORE TABLE `PREFIX_product_comment_criterion_product` ADD PRIMARY KEY(`id_product`, `id_product_comment_criterion`);

/* ##################################### */
/* 					CONTENTS					 */
/* ##################################### */
