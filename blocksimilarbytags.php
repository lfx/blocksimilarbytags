<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class blocksimilarbytags extends Module
{
    public function __construct()
    {
        $this->name = 'blocksimilarbytags';
        $this->tab = 'front_office_features';
        $this->version = '0.0.1';
        $this->author = 'Liudas S';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Similar Products By Tags');
        $this->description = $this->l('Displays similar product by tags.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||
            !$this->registerHook('displayProductTab')
        ) {
            return false;

        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookDisplayProductTab($params)
    {

        $prefix = $this->getPrefix();
        $this->context->controller->addCSS($this->_path.'blocksimilarbytags.css', 'all');
        $org_id = $params['product']->id;
        $lang_id = $this->context->language->id;
        $product_ids = $this->getSimilarProducts($params['product']->tags[$lang_id]);

        $products = array();
        foreach ($product_ids as $p_key => $product_id) {
            $size = ImageType::getFormatedName('medium');
            $product = new Product($product_id, true, $lang_id, $this->context->shop->id);
            $link = new Link();
            $product_url = $link->getProductLink($product);

            $product_url = $product_url."?sim_from=".$org_id;

            $images = $product->getImages($lang_id);
            foreach ($images as $key => $val) {
                if ($val['cover'] === '1') {
                    $img_id = $val['id_image'];
                    break;
                }
            }
            $image = $link->getImageLink($product->link_rewrite, $img_id, $size);

            $products[$p_key] = array('url' => $product_url, 'img' => $prefix . $image);
        }
        $this->smarty->assign(array(
            'tag_products' => $products
        ));

        return $this->display(__FILE__, 'productsbytags.tpl');
    }

    private function getSimilarProducts($tags)
    {
        $tags_in = '"' . implode('","', $tags) . '"';
        $sql = '
          SELECT distinct(pt.id_product)
          FROM ' . _DB_PREFIX_ . 'tag tg
          INNER JOIN ' . _DB_PREFIX_ . 'product_tag pt ON pt.id_tag = tg.id_tag
          WHERE tg.name IN (' . $tags_in . ')  ORDER BY RAND() LIMIT 6;
        ';

        if (!$tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) return false;
        $result = array();
        foreach ($tmp as $val) {
            $result[] = $val['id_product'];
        }
        return $result;
    }

    private function getPrefix()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $prefix = "https://";
        } else {
            $prefix = "http://";
        }
        return $prefix;
    }

}
