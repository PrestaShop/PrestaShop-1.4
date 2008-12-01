<?php

class Gsitemap extends Module
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'gsitemap';
        $this->tab = 'Tools';
        $this->version = 1.0;

        $this->_directory = dirname(__FILE__).'/../../';
        $this->_filename = $this->_directory.'sitemap.xml';
        $this->_filename_http = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'sitemap.xml';

        parent::__construct();

        /* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Google sitemap');
        $this->description = $this->l('Generate your Google sitemap file');
    }

    function uninstall()
    {
        @file_put_contents($this->_filename, '');
        parent::uninstall();
		
		// Hack for 'gsitemap' to uninstall it correctly
		Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'module`
				WHERE `id_module` = '.intval($this->id));
    }
    private function _postValidation()
    {
		@unlink($this->_filename);
		if (!$fp = @fopen($this->_filename, 'w'))
			$this->_postErrors[] = $this->l('Cannot create').' '.realpath(dirname(__FILE__.'/../..')).'/'.$this->l('sitemap.xml file.');
		else
			fclose($fp);
    }

    private function _postProcess()
    {
		$link = new Link();
        $fp = fopen($this->_filename, 'w');
        $xml = new SimpleXMLElement('<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"></urlset>');

        $categories = Db::getInstance()->ExecuteS('
		SELECT c.id_category, c.level_depth, link_rewrite, DATE_FORMAT(date_add, \'%Y-%m-%d\') AS date_add, cl.id_lang
		FROM '._DB_PREFIX_.'category c
		LEFT JOIN '._DB_PREFIX_.'category_lang cl ON c.id_category = cl.id_category
		LEFT JOIN '._DB_PREFIX_.'lang l ON cl.id_lang = l.id_lang
		WHERE l.`active` = 1');
		foreach($categories as $category)
        {
			if (($priority = 0.9 - ($category['level_depth'] / 10)) < 0.1)
				$priority = 0.1;
			$sitemap = $xml->addChild('url');
            $sitemap->addChild('loc', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').htmlspecialchars($link->getCategoryLink($category['id_category'], $category['link_rewrite']).'&id_lang='.intval($category['id_lang'])));
            $sitemap->addChild('priority', $priority);
            $sitemap->addChild('lastmod', $category['date_add']);
            $sitemap->addChild('changefreq', 'monthly');
        }

        $products = Db::getInstance()->ExecuteS('
		SELECT p.id_product, pl.link_rewrite, DATE_FORMAT(date_add, \'%Y-%m-%d\') AS date_add, pl.id_lang, cl.`link_rewrite` AS category, (
			SELECT MIN(level_depth)
			FROM '._DB_PREFIX_.'product p2
			LEFT JOIN '._DB_PREFIX_.'category_product cp2 ON p2.id_product = cp2.id_product
			LEFT JOIN '._DB_PREFIX_.'category c2 ON cp2.id_category = c2.id_category
			WHERE p2.id_product = p.id_product) AS level_depth
		FROM '._DB_PREFIX_.'product p
		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND pl.`id_lang` = cl.`id_lang`)
		LEFT JOIN '._DB_PREFIX_.'lang l ON cl.id_lang = l.id_lang
		WHERE l.`active` = 1');
        foreach($products as $product)
        {
			if (($priority = 0.7 - ($product['level_depth'] / 10)) < 0.1)
				$priority = 0.1;
            $sitemap = $xml->addChild('url');
            $sitemap->addChild('loc', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').htmlspecialchars($link->getProductLink($product['id_product'], $product['link_rewrite'], $product['category']).'&id_lang='.intval($category['id_lang'])));
            $sitemap->addChild('priority', $priority);
            $sitemap->addChild('lastmod', $product['date_add']);
            $sitemap->addChild('changefreq', 'weekly');
        }

        $xmlString = $xml->asXML();
        fwrite($fp, $xmlString, Tools::strlen($xmlString));
        fclose($fp);

        $res = file_exists($this->_filename);
        $this->_html .= '<h3 class="'. ($res ? 'conf confirm' : 'alert error') .'" style="margin-bottom: 20px">';
        $this->_html .= $res ? $this->l('Sitemap file successfully generated') : $this->l('Error while creating sitemap file');
        $this->_html .= '</h3>';
    }

    private function _displaySitemap()
    {
        if (file_exists($this->_filename))
        {			
            $fp = fopen($this->_filename, 'r');
            $fstat = fstat($fp);
            fclose($fp);
            $xml = simplexml_load_file($this->_filename);
            $nbPages = sizeof($xml->url);

            $this->_html .= '<p>'.$this->l('Your Google sitemap file is online at the following address:').'<br />
            <a href="'.$this->_filename_http.'"><b>'.$this->_filename_http.'</b></a></p><br />';

            $this->_html .= $this->l('Update:').' <b>'.strftime('%A %d %B %Y %H:%M:%S').'</b><br />';
            $this->_html .= $this->l('Filesize:').' <b>'.number_format(($fstat['size']*.000001), 3).'mo</b><br />';
            $this->_html .= $this->l('Indexed pages:').' <b>'.$nbPages.'</b><br /><br />';
        }
    }

    private function _displayForm()
    {
        $this->_html .=
        '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<input name="btnSubmit" class="button" value="'.((!file_exists($this->_filename)) ? $this->l('Generate sitemap file') : $this->l('Update sitemap file')).'" type="submit" />
        </form>';
    }
    
    function getContent()
    {
        $this->_html .= '<h2>'.$this->l('Search Engine Optimization').'</h2>';

        $this->_html .= $this->l('See').' <a href="https://www.google.com/webmasters/tools/docs/en/about.html"> '.$this->l('this page').'</a> '.$this->l('for more information').'<br /><br />';

        if (!empty($_POST))
        {
            $this->_postValidation();
            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors AS $err)
                    $this->_html .= '<div class="alert error">'.$err.'</div>';
        }
        else
            $this->_html .= '<br />';

        $this->_displaySitemap();
        $this->_displayForm();

        return $this->_html;
    }
}


?>
