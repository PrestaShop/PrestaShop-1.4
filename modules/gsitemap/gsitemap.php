<?php

class Gsitemap extends Module
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'gsitemap';
        $this->tab = 'Tools';
        $this->version = 1.3;

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
        file_put_contents($this->_filename, '');
        parent::uninstall();
		
		// Hack for 'gsitemap' to uninstall it correctly
		Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'module`
				WHERE `id_module` = '.intval($this->id));
    }
    private function _postValidation()
    {
        file_put_contents($this->_filename, '');
		if (!$fp = fopen($this->_filename, 'w'))
			$this->_postErrors[] = $this->l('Cannot create').' '.realpath(dirname(__FILE__.'/../..')).'/'.$this->l('sitemap.xml file.');
		else
			fclose($fp);
    }
	
	private function getUrlWith($url, $key, $value)
	{
		if (strpos($url, '?') !== false)
			return $url.'&'.$key.'='.$value;
		return $url.'?'.$key.'='.$value;
	}

    private function _postProcess()
    {
		$link = new Link();
        $fp = fopen($this->_filename, 'w');
		
        $xml = new SimpleXMLElement('<urlset
			xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
			http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
		</urlset>');

		$sitemap = $xml->addChild('url');
		$sitemap->addChild('loc', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__);
		$sitemap->addChild('priority', '1.00');
		$sitemap->addChild('lastmod', date("Y-m-d"));
		$sitemap->addChild('changefreq', 'daily');
		
		$cmss = Db::getInstance()->ExecuteS('
		SELECT DISTINCT b.id_cms, cl.link_rewrite, cl.id_lang
		FROM '._DB_PREFIX_.'block_cms b
		LEFT JOIN '._DB_PREFIX_.'cms_lang cl ON b.id_cms = cl.id_cms
		LEFT JOIN '._DB_PREFIX_.'lang l ON cl.id_lang = l.id_lang
		WHERE l.`active` = 1');

      	foreach($cmss AS $cms)
      	{
			$sitemap = $xml->addChild('url');
            $sitemap->addChild('loc', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').htmlspecialchars($this->getUrlWith($link->getCMSLink($cms['id_cms'], $cms['link_rewrite']), 'id_lang', intval($cms['id_lang']))));
            $sitemap->addChild('priority', '0.9');
            $sitemap->addChild('changefreq', 'monthly');
		}

        $categories = Db::getInstance()->ExecuteS('
		SELECT c.id_category, c.level_depth, link_rewrite, DATE_FORMAT(IF(date_upd,date_upd,date_add), \'%Y-%m-%d\') AS date_upd, cl.id_lang
		FROM '._DB_PREFIX_.'category c
		LEFT JOIN '._DB_PREFIX_.'category_lang cl ON c.id_category = cl.id_category
		LEFT JOIN '._DB_PREFIX_.'lang l ON cl.id_lang = l.id_lang
		WHERE l.`active` = 1 AND c.`active` = 1 AND c.id_category !=1
		ORDER BY date_upd DESC');
		foreach($categories as $category)
        {
			if (($priority = 0.9 - ($category['level_depth'] / 10)) < 0.1)
				$priority = 0.1;
			$sitemap = $xml->addChild('url');
            $sitemap->addChild('loc', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').htmlspecialchars($this->getUrlWith($link->getCategoryLink($category['id_category'], $category['link_rewrite']), 'id_lang', intval($category['id_lang']))));
            $sitemap->addChild('priority', $priority);
            $sitemap->addChild('lastmod', $category['date_upd']);
            $sitemap->addChild('changefreq', 'daily');
      	}

        $products = Db::getInstance()->ExecuteS('
		SELECT p.id_product, pl.link_rewrite, DATE_FORMAT(IF(date_upd,date_upd,date_add), \'%Y-%m-%d\') AS date_upd, pl.id_lang, cl.`link_rewrite` AS category, ean13, (
			SELECT MIN(level_depth)
			FROM '._DB_PREFIX_.'product p2
			LEFT JOIN '._DB_PREFIX_.'category_product cp2 ON p2.id_product = cp2.id_product
			LEFT JOIN '._DB_PREFIX_.'category c2 ON cp2.id_category = c2.id_category
			WHERE p2.id_product = p.id_product) AS level_depth
		FROM '._DB_PREFIX_.'product p
		LEFT JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND pl.`id_lang` = cl.`id_lang`)
		LEFT JOIN '._DB_PREFIX_.'lang l ON pl.id_lang = l.id_lang
		WHERE l.`active` = 1 AND p.`active` = 1
		ORDER BY date_upd DESC');
        foreach($products as $product)
        {
			if (($priority = 0.7 - ($product['level_depth'] / 10)) < 0.1)
				$priority = 0.1;
            $sitemap = $xml->addChild('url');
            $sitemap->addChild('loc', 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').htmlspecialchars($this->getUrlWith($link->getProductLink($product['id_product'], $product['link_rewrite'], $product['category'], $product['ean13']), 'id_lang', intval($product['id_lang']))));
            $sitemap->addChild('priority', $priority);
            $sitemap->addChild('lastmod', $product['date_upd']);
            $sitemap->addChild('changefreq', 'daily');
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
        if (file_exists($this->_filename) AND filesize($this->_filename))
        {			
            $fp = fopen($this->_filename, 'r');
            $fstat = fstat($fp);
            fclose($fp);
            $xml = simplexml_load_file($this->_filename);
            $nbPages = sizeof($xml->url);

            $this->_html .= '<p>'.$this->l('Your Google sitemap file is online at the following address:').'<br />
            <a href="'.$this->_filename_http.'"><b>'.$this->_filename_http.'</b></a></p><br />';

            $this->_html .= $this->l('Update:').' <b>'.strftime('%A %d %B %Y %H:%M:%S',$fstat['mtime']).'</b><br />';
            $this->_html .= $this->l('Filesize:').' <b>'.number_format(($fstat['size']*.000001), 3).'mo</b><br />';
            $this->_html .= $this->l('Indexed pages:').' <b>'.$nbPages.'</b><br /><br />';
        }
    }

    private function _displayForm()
    {
        $this->_html .=
        '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<input name="btnSubmit" class="button" type="submit"
			value="'.((!file_exists($this->_filename)) ? $this->l('Generate sitemap file') : $this->l('Update sitemap file')).'" />
        </form>';
    }
    
    function getContent()
    {
        $this->_html .= '<h2>'.$this->l('Search Engine Optimization').'</h2>
		'.$this->l('See').' <a href="https://www.google.com/webmasters/tools/docs/en/about.html">
		'.$this->l('this page').'</a> '.$this->l('for more information').'<br /><br />';
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
