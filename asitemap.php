<?php
if (!defined('_PS_VERSION_'))
  exit;

class ASiteMap extends Module
{
    protected $_settings = array();



    public function __construct()
    {
      $this->name = 'asitemap';
      $this->tab = 'seo';
      $this->version = '1.0.0';
      $this->author = 'KS email: 7996326@gmail.com';
      $this->need_instance = 0;
      $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
      $this->bootstrap = true;


      $this->displayName = $this->l('Sitemap generator');
      $this->description = $this->l('Advanced sitemap generator');

      $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');


      $this->host = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ? "https://":"http://";
      $this->host .= Tools::getHttpHost(false,true) . __PS_BASE_URI__;

      $this->c_f_options = array(
        array(
          'id' => 0,
          'name' => 'always'
        ),
        array(
          'id' => 1,
          'name' => 'hourly'
        ),
        array(
          'id' => 2,
          'name' => 'daily'
        ),
        array(
          'id' => 3,
          'name' => 'weekly'
        ),
        array(
          'id' => 4,
          'name' => 'monthly'
        ),
        array(
          'id' => 5,
          'name' => 'yearly'
        ),
        array(
          'id' => 6,
          'name' => 'never'
        ),
      );

    parent::__construct();
  }


  public function install()
  {

    $sql = array();

    include(dirname(__FILE__).'/sql/install.php');
    foreach ($sql as $s)
      if (!Db::getInstance()->execute($s))
        return false;


        if (!parent::install() )
      			return false;

        return true;
  }

    public function uninstall()
  {

    $sql = array();

   include(dirname(__FILE__).'/sql/uninstall.php');
  		foreach ($sql as $s)
  			if (!Db::getInstance()->execute($s))
  				return false;



    if (!parent::uninstall()
       )

        return false;

      return true;
  }


protected function getModuleDbSettings($type = 1)
{

  if ((int)$type==1) {
    $result_array = array();

    $sql = 'SELECT `param_name`, `param_value` FROM `'._DB_PREFIX_.'asitemap_conf` WHERE 1<3';

    if ($results = Db::getInstance()->ExecuteS($sql))
        foreach ($results as $row)
    $result_array[(string) $row['param_name']] = $row['param_value'];

    return $result_array;
  }
  elseif ((int)$type==2)  {

    $sql = 'SELECT `page_id`, `page_name`, `page_type`, `page_link`,  `page_priority`, `page_frequency` FROM `'._DB_PREFIX_.'asitemap_pages` WHERE 1<3';

    if ($results = Db::getInstance()->ExecuteS($sql))
        return $results;

  }
  return false;
}






public function getContent()
{

    $output = null;

    $id_lang = (int)Context::getContext()->language->id;
    $languages = $this->context->controller->getLanguages();
    $default_language = (int)Configuration::get('PS_LANG_DEFAULT');




    if (Tools::isSubmit('sm_settings'.$this->name))
    {
        $sm_enabled = (string)Tools::getValue('sitemap_enabled');
        $sitemap_link = (string)Tools::getValue('asitemap_link');

        $p_p_enabled = (string)Tools::getValue('p_p_enabled');
        $p_p_changefreq = (string)Tools::getValue('p_p_changefreq');


        $p_p_priority = (string)Tools::getValue('p_p_priority');

        $i_enabled = (string)Tools::getValue('i_enabled');

        // $def_img_cap = array();

        // foreach ($languages as $lang) {
        //     $def_img_cap['def_img_cap_'.$lang['id_lang']]=(string)Tools::getValue('def_img_cap_'.$lang['id_lang']);
        // }

        $cms_pages = array();
        $category_pages = array();
        $manual_pages = array();


        //error_log(print_r(Tools::getValue('selected_pages'),true));
         // error_log(print_r($_POST,true));


//        $html['data'] .= '[false,"'.$value['id_cms'].'","cms","'.htmlspecialchars($value['meta_title']).'","monthly","0.5"],';

        foreach (Tools::getValue('selected_cms') as $value) {

          $ar = explode(',',$value);

          array_push($cms_pages, $ar);

        }

        foreach (Tools::getValue('selected_categories') as $value) {

          $ar = explode(',',$value);

          array_push($category_pages, $ar);

        }

        foreach (Tools::getValue('selected_manual') as $value) {

          $ar = explode(',',$value);
          if (trim($ar[3]) != '')
            array_push($manual_pages, $ar);

        }

        // $err=$category_pages;
        // $fp = fopen( dirname(__FILE__).'/dbg/results'.date('Y_m_d_H_i_s',time()).'.json', 'w');
        //  fwrite($fp, json_encode($err));
        //  fclose($fp);


        if (!$sitemap_link ||  !Validate::isGenericName($sitemap_link)
         || strpos($sitemap_link,$this->host) === false
        )
          $output .= $this->displayError($this->l('Invalid Configuration value'));
        elseif ( !Validate::isFloat($p_p_priority) || (float)$p_p_priority<0 || (float)$p_p_priority>1) {
          $output .= $this->displayError($this->l('Product pages sitemap priority value must be between 0.0 and 1.0'));
        }

        else
        {
          Db::getInstance()->update('asitemap_conf', array(
                'param_value' => $sm_enabled
              ),'param_name= \'sitemap_enabled\'');

          Db::getInstance()->update('asitemap_conf', array(
                'param_value' =>  str_replace($this->host,'',$sitemap_link),
              ),'param_name= \'asitemap_link\'');


          Db::getInstance()->update('asitemap_conf', array(
                'param_value' => $p_p_enabled
              ),'param_name= \'p_p_enabled\'');


          Db::getInstance()->update('asitemap_conf', array(
                'param_value' =>  $this->c_f_options[$p_p_changefreq]['name']
              ),'param_name= \'p_p_changefreq\'');

          Db::getInstance()->update('asitemap_conf', array(
                'param_value' => number_format($p_p_priority, 1, '.', '')
              ),'param_name= \'p_p_priority\'');

          Db::getInstance()->update('asitemap_conf', array(
                'param_value' => $i_enabled
              ),'param_name= \'i_enabled\'');

          Db::getInstance()->delete('asitemap_pages');
          foreach ($cms_pages as $value) {

            Db::getInstance()->insert('asitemap_pages', array(
               'page_id' => $value[1],
               'page_type' => $value[2],
               'page_name' => urldecode($value[3]),
               'page_link' => '',
               'page_frequency' => $value[4],
               'page_priority' => $value[5],

             ));
          }
          foreach ($category_pages as $value) {

            Db::getInstance()->insert('asitemap_pages', array(
               'page_id' => $value[1],
               'page_type' => $value[2],
               'page_name' => urldecode($value[3]),
               'page_link' => '',
               'page_frequency' => $value[4],
               'page_priority' => $value[5],

             ));
          }
          foreach ($manual_pages as $value) {

            Db::getInstance()->insert('asitemap_pages', array(
               'page_id' => $value[1],
               'page_type' => $value[2],
               'page_name' => urldecode($value[3]),
               'page_link' => urldecode($value[3]),
               'page_frequency' => $value[4],
               'page_priority' => $value[5],

             ));
          }
          $output .= $this->displayConfirmation($this->l('Settings updated'));

        }
    }

    if (Tools::isSubmit('submitGenerate'))
    {

      $this->generateSitemap();

      $output .= $this->displayConfirmation($this->l('Sitemap has been generated'));

    }


    $this->_settings=$this->getModuleDbSettings();




    return $output.$this->displayForm();

}

public function displayForm()
{
// Get default language
$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');



// Init Fields form array
$fields_form[0]['form'] = array(
    'legend' => array(
        'title' => $this->l('Sitemap settings'),
         'icon' => 'icon-cogs',
    ),
    'input' => array(
        array(
            'type' => 'switch',
            'label' => $this->l('Sitemap enabled'),
            'name' => 'sitemap_enabled',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
        ),
        array(
            'type' => 'switch',
            'label' => $this->l('Generate file for products'),
            'name' => 'p_p_enabled',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
        ),

        array(
          'type' => 'select',
          'label' => $this->l('Product pages sitemap changes frequency'),
          'name' => 'p_p_changefreq',
          'required' => true,
          'options' => array(
            'query' => $this->c_f_options,
            'id' => 'id',
            'name' => 'name'
          )
        ),
        array(
            'type' => 'html',
            'label' => $this->l('Product pages sitemap priority'),
            'desc' => $this->l('default value for priority is 0.5'),
            'name' => 'p_p_priority',
            'required' => true,
            'html_content' => '<input min="0" max="1" step="0.1" pattern="\d*" type="number" name="p_p_priority" value="'.$this->_settings['p_p_priority'].'">'
        ),

        array(
            'type' => 'switch',
            'label' => $this->l('Generate file for images'),
            'name' => 'i_enabled',
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                )
            ),
        ),

        array(
            'type' => 'p_list_choice',
            'label' => $this->l('List of other pages for including to sitemap'),
            'name' => 'p_list',
  //          'html_content' => $p_list_html,
        ),

    ),
    'submit' => array(
        'name' => 'sm_settings'.$this->name,
        'title' => $this->l('Save'),
        'class' => 'btn btn-default pull-right'
    )
);

$fields_form[1]['form'] = array(
    'legend' => array(
        'title' => $this->l('Links'),
    ),
    'input' => array(
        array(
            'type' => 'text',
            'label' => $this->l('Sitemap link'),
            'name' => 'asitemap_link',
            'size' => 20,
            'readonly' => false,
            'required' => true
        ),


    ),

    'submit' => array(
        'title' => $this->l('Generate file'),
        'name' => 'submitGenerate',
        'class' => 'btn btn-default pull-right',
        'icon' => 'process-icon-update'
    )
);


   $helper = new HelperForm();
   $helper->show_toolbar = true;
   $helper->table =  $this->table;
   $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

   $helper->default_form_language = $lang->id;

   $helper->module = $this;
   $helper->name_controller = $this->name;
   $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

//   $helper->identifier = $this->identifier;
   //$helper->submit_action = 'submitmymoduleconf';

   $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
   $helper->token = Tools::getAdminTokenLite('AdminModules');

   $options[0]=$this->makePagesOption(0);
   $options[1]=$this->makePagesOption(1);

   $helper->tpl_vars = array(
   'uri' => $this->getPathUri(),
   'languages' => $this->context->controller->getLanguages(),
   'id_language' => $this->context->language->id,
   'selected_pages' => $options[1]['data'],
   'available_pages' => $options[0]['data'],
   'row_params' => $options[0]['row_params'],
   'jstable' => $this->host. 'modules/'.$this->name.'/js/jquery.edittable.js',
   'jspag' => $this->host. 'modules/'.$this->name.'/js/pag.js',
   'jstabsui' => $this->host. 'js/jquery/ui/jquery.ui.tabs.min.js',
   'page_tabs' => array(
                        array(
                          'id' => 'cms',
                          'name' =>  $this->l('CMS Pages')
                        ),
                        array(
                          'id' => 'categories',
                          'name' =>  $this->l('Categories')
                        ),
                        array(
                          'id' => 'manual',
                          'name' =>  $this->l('Manualy added')
                        ),
                      ),



   'csstable' => $this->host. 'modules/'.$this->name.'/css/jquery.edittable.css',
   'cssui' => $this->host. 'js/jquery/ui/themes/base/jquery.ui.all.css',
   'frequency' =>$this->c_f_options,
    );

    //get setting
   $helper->fields_value['sitemap_enabled'] = $this->_settings['sitemap_enabled'];
   $helper->fields_value['asitemap_link'] =  $this->host . $this->_settings['asitemap_link'];

   $helper->fields_value['p_p_enabled'] = $this->_settings['p_p_enabled'];
   $helper->fields_value['p_p_changefreq'] = array_search($this->_settings['p_p_changefreq'],array_column($this->c_f_options,'name'));
   $helper->fields_value['f_test'] = '3';

   $helper->fields_value['i_enabled'] = $this->_settings['i_enabled'];


   // foreach (Language::getLanguages(false) as $lang) {
   //     $helper->fields_value['def_img_cap'][$lang['id_lang']] = isset($this->_settings['def_img_cap_'.$lang['id_lang']]) ? $this->_settings['def_img_cap_'.$lang['id_lang']] : '';
   // }


   //$helper->fields_value['p_changefreq'] = array_search($this->_settings['p_changefreq'],array_column($this->c_f_options,'name'));



return $helper->generateForm($fields_form);
}


protected function makePagesOption($type)
{

  $id_lang = (int)Context::getContext()->language->id;
  $cms = CMS::getLinks($id_lang);

  $categories =$this->getCategories($id_lang);
  $html['data'] ='{';

  $pages = $this->getModuleDbSettings(2);


  $html['row_params'] = '{';
  $html['row_params'] .= "'cms':[true,false,false,false,true,true],";
  $html['row_params'] .= "'categories':[true,false,false,false,true,true],";
  $html['row_params'] .= "'manual':[true,false,false,true,true,true],";
  $html['row_params'] .= '}';


if ((int)$type==1) { //selected_pages




//cms

$html['data'] .= "cms: [";

 foreach ($pages as $value) {
   if ($value['page_type']=='cms')
    $html['data'] .= '[false,"'.$value['page_id'].'","'.$value['page_type'].'","'.htmlspecialchars($value['page_name']).'","'.$value['page_frequency'].'","'.$value['page_priority'].'"],';
  }
$html['data'] .= "],";

$html['data'] .= "categories: [";
  foreach ($pages as $value) {
    if ($value['page_type']=='categories')
     $html['data'] .= '[false,"'.$value['page_id'].'","'.$value['page_type'].'","'.htmlspecialchars($value['page_name']).'","'.$value['page_frequency'].'","'.$value['page_priority'].'"],';
   }
$html['data'] .= "],";

$html['data'] .= "manual: [";
  foreach ($pages as $value) {
     if ($value['page_type']=='manual')
      $html['data'] .= '[false,"'.$value['page_id'].'","'.$value['page_type'].'","'.htmlspecialchars($value['page_name']).'","'.$value['page_frequency'].'","'.$value['page_priority'].'"],';
    }

$html['data'] .= "],";


}



elseif ((int)$type==0) {



$html['data'] .= "cms: [";
  foreach ($cms as $value) {
      $fnd=0;

      foreach ($pages as $page) {
    if (($page['page_type']=='cms') && (int)$page['page_id']==(int)$value['id_cms'])
           $fnd=1;
        }

        if ($fnd==0) {
          $html['data'] .= '[false,"'.$value['id_cms'].'","cms","'.htmlspecialchars($value['meta_title']).'","monthly","0.5"],';

        }
  }
$html['data'] .= "],";

  $html['data'] .= "categories: [";
  foreach ($categories as $category) {
    $fnd=0;

    foreach ($pages as $page) {
      if (($page['page_type']=='categories') && (int)$page['page_id']==(int)$category['id_category'])
       $fnd=1;
      }
      if ($fnd==0) {
      $html['data'] .= '[false,"'.$category['id_category'].'","categories","'.htmlspecialchars($category['name']).'","weekly","0.9"],';

      }
  }
  $html['data'] .= "],";
}

$html['data'] =$html['data']. '}';
    return $html;

}


protected function getCategories($type)
{


  $id_lang = (int)$this->context->language->id;
  $id_shop = (int)Shop::getContextShopID();

//dinamicaly geting chosen groups

//geting all groups

 $sql = 'SELECT
         @r:=c.id_category id_category,
          c.id_parent,
         concat(lpad(\'\',c.level_depth,\'-\'),l.name) as name,
         reverse(concat(c.id_category,\',\',((select GROUP_CONCAT(@r:=(SELECT
                                 c1.id_parent
                             FROM
                                 '._DB_PREFIX_.'category c1
                             WHERE
                                 c1.id_category = @r)) AS p
         from '._DB_PREFIX_.'category c2
         )))) parents

     FROM
         '._DB_PREFIX_.'category c,
         '._DB_PREFIX_.'category_lang l,
         (select @r:=0) Z
    where c.id_category=l.id_category and  l.`id_lang`='.$id_lang.' and l.`id_shop`='.$id_shop;
    $sql .= '  ';

    $sql .= 'group by c.id_category, c.id_parent, l.name
 order by 4';



  if ($results = Db::getInstance()->ExecuteS($sql)) {


    return $results;
  }

}




protected function generateSitemap()
{

 $this->_settings=$this->getModuleDbSettings();

 $default_language = (int)Configuration::get('PS_LANG_DEFAULT');


        //$product_list = Product::getProducts($this->id_lang, 0,0, 'id_category', 'ASC', (int)$value, true);

 $sitemap_text = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                 '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

 $langs = Language::getLanguages(true);

 foreach ($langs as $key => $value) {

    if ((int)$this->_settings['p_p_enabled']==1) {

      $sitemap_text .= ' <sitemap><loc>'.$this->host.'sitemap-products-'.$value['iso_code'].'.xml'.'</loc></sitemap>'.PHP_EOL;
      $this->generateProductsSitemap($value['id_lang'],$value['iso_code']);
    }


    if (((int)$this->_settings['i_enabled']==1) && $default_language==(int)$value['id_lang']) {

      $sitemap_text .= ' <sitemap><loc>'.$this->host.'sitemap-images-'.$value['iso_code'].'.xml'.'</loc></sitemap>'.PHP_EOL;
      $this->generateImagesSitemap($value['id_lang'],$value['iso_code']);

    }



      $sitemap_text .= ' <sitemap><loc>'.$this->host.'sitemap-pages-'.$value['iso_code'].'.xml'.'</loc></sitemap>'.PHP_EOL;
      $this->generatePagesSitemap($value['id_lang'],$value['iso_code']);



  }

  $sitemap_text .= '</sitemapindex>';

  $write_fd = fopen(_PS_ROOT_DIR_.'/'.$this->_settings['asitemap_link'], 'w');
  fwrite($write_fd, $sitemap_text);
  fclose($write_fd);

}


protected function generateProductsSitemap($id_lang,$iso_lang)
{

  //error_log(print_r((int)$id_lang,true));


  $sitemap_text = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                   '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

  $plist = null;
  $plist = Product::getProducts((int)$id_lang, 0, 0, 'id_product', 'asc', false, true);



foreach ($plist as $product) {

  $link = new Link();
  $url = $link->getProductLink($product, null, null, null,  $id_lang);
  $date = new DateTime($product['date_upd']);

   $sitemap_text .= '<url><loc>'.$url.'/</loc><lastmod>'.$date->format('Y-m-d').'</lastmod><changefreq>'.$this->_settings['p_p_changefreq'].'</changefreq><priority>'.$this->_settings['p_p_priority'].'</priority></url>'.PHP_EOL;

}

$sitemap_text .= '</urlset>';


    $write_fd = fopen(_PS_ROOT_DIR_.'/sitemap-products-'.$iso_lang.'.xml', 'w');
    fwrite($write_fd, $sitemap_text);
    fclose($write_fd);


}







protected function generateImagesSitemap($id_lang,$iso_lang)
{



   $sitemap_text = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                   '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'.PHP_EOL;

  $plist = null;
  $plist = Product::getProducts((int)$id_lang, 0, 0, 'id_product', 'asc', false, true);



  foreach ($plist as $product) {

    $link = new Link();
    $url = $link->getProductLink($product, null, null, null,  $id_lang);

    $images = $this->getProductImages($product['id_product'],$id_lang);

    if (!empty($images)) {

      $sitemap_text .= '<url><loc>'.$url.'/</loc>'.PHP_EOL;

    }
  //
  // $err=$images;
  // $fp = fopen( dirname(__FILE__).'/dbg/results'.date('Y_m_d_H_i_s',time()).'.json', 'w');
  //  fwrite($fp, json_encode($err));
  //  fclose($fp);




    foreach ($images as $image) {

    $img_url = array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ? "https://":"http://";
    $img_url .= $link->getImageLink($product['link_rewrite'], $image['id_image'], 'large_default');


    //$caption = (trim($image['legend'])=='') ? $this->_settings['def_img_cap_'.$id_lang] : trim($image['legend']);

    $sitemap_text .= '<image:image>'.PHP_EOL.
                     '<image:loc>'.$img_url.'</image:loc>'.PHP_EOL.
                     '<image:title>'.htmlspecialchars(trim($product['name'])).'</image:title>'.PHP_EOL. //name of the product
    //                 '<image:caption>'.htmlspecialchars($caption).'</image:caption>'.PHP_EOL.  // caption for image if blank will be value of default image caption text parameter def_img_cap
                     '</image:image>'.PHP_EOL;

    }

    if (!empty($images)) {
      $sitemap_text .= '</url>'.PHP_EOL;
    }

  }

  $sitemap_text .= '</urlset>';
    // $err=$plist;
    // $fp = fopen( dirname(__FILE__).'/dbg/results'.date('Y_m_d_H_i_s',time()).'.json', 'w');
    //  fwrite($fp, json_encode($err));
    //  fclose($fp);

  $write_fd = fopen(_PS_ROOT_DIR_.'/sitemap-images-'.$iso_lang.'.xml', 'w');
  fwrite($write_fd, $sitemap_text);
  fclose($write_fd);


}


  private function getProductImages($id_product, $id_lang = 0){



    $sql = 'SELECT i.`id_image`, il.legend FROM `'._DB_PREFIX_.'image` i
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (il.id_image = i.id_image) AND (il.id_lang='.(int)($id_lang).')'.
            'WHERE `id_product` = '.(int)($id_product);

    if ($results = Db::getInstance()->ExecuteS($sql)) {
        return $results;

        }
  }



  protected function generatePagesSitemap($id_lang,$iso_lang)
  {


    $pages=$this->getModuleDbSettings(2);

    $sitemap_text = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
                     '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

    $cms_pages = array();

    $category_pages = array();
    $manual_pages = array();


     foreach ($pages as $page) {

         if (trim($page['page_type']) == 'cms') {


           array_push($cms_pages, $page);

         }

        if (trim($page['page_type']) == 'categories') {

           array_push($category_pages, $page);

        }
        if (trim($page['page_type']) == 'manual') {

           array_push($manual_pages, $page);

        }
     }



  $date = new DateTime('now');
  $link = new Link();

  foreach ($cms_pages as $value) {


    $cms = CMS::getLinks($id_lang,array($value['page_id']))[0];





    $cms['link'] = $link->getCMSLink((int)($cms['id_cms']), $cms['link_rewrite'], null, $id_lang);
    $sitemap_text .= '<url><loc>'.$cms['link'].'/</loc><lastmod>'.$date->format('Y-m-d').'</lastmod><changefreq>'.$value['page_frequency'].'</changefreq><priority>'.$value['page_priority'].'</priority></url>'.PHP_EOL;

  }

  foreach ($category_pages as $value) {

    $cat =  new Category((int)$value['page_id'],$id_lang);

    if ($cat->active==1) {

    $cat_link = $link->getCategoryLink((int)$value['page_id'], null, $id_lang);
    $sitemap_text .= '<url><loc>'.$cat_link.'/</loc><lastmod>'.$date->format('Y-m-d').'</lastmod><changefreq>'.$value['page_frequency'].'</changefreq><priority>'.$value['page_priority'].'</priority></url>'.PHP_EOL;


    }
  }

  foreach ($manual_pages as $value) {


    $sitemap_text .= '<url><loc>'.rtrim($value['page_link'],'/').'/</loc><lastmod>'.$date->format('Y-m-d').'</lastmod><changefreq>'.$value['page_frequency'].'</changefreq><priority>'.$value['page_priority'].'</priority></url>'.PHP_EOL;



  }

  $sitemap_text .= '</urlset>';


      $write_fd = fopen(_PS_ROOT_DIR_.'/sitemap-pages-'.$iso_lang.'.xml', 'w');
      fwrite($write_fd, $sitemap_text);
      fclose($write_fd);


}



}
