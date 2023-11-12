<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\BestSales\BestSalesProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class BlockFilterProductBestSellers extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'blockfilterproductbestsellers';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Doryan Fourrichon';
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        
        //récupération du fonctionnement du constructeur de la méthode __construct de Module
        parent::__construct();
        $this->bootstrap = true;

        $this->displayName = $this->l('Block Filter Product Best Sellers');
        $this->description = $this->l('Module qui affiche les produits les plus vendus');

        $this->confirmUninstall = $this->l('Do you want to delete this module');


    }

    public function install()
    {
        if (!parent::install() ||
        !Configuration::updateValue('ACTIVATE_BLOC_CATEGORIES',0) ||
        !Configuration::updateValue('PRODUCT_NUMBER_CATEGORIES',4) ||
        !Configuration::updateValue('ACTIVATE_BLOC_MARQUE',0) ||
        !Configuration::updateValue('PRODUCT_NUMBER_MARQUE', 4) ||
        !$this->registerHook('displayHeaderCategory')
        ) {
            return false;
        }
            return true;
        
    }

    public function uninstall()
    {
        if(!parent::uninstall() ||
        !Configuration::deleteByName('ACTIVATE_BLOC_CATEGORIES') ||
        !Configuration::deleteByName('PRODUCT_NUMBER_CATEGORIES') ||
        !Configuration::deleteByName('ACTIVATE_BLOC_MARQUE') ||
        !Configuration::deleteByName('PRODUCT_NUMBER_MARQUE') ||
        !$this->unregisterHook('displayHeaderCategory')
        )
        {
            return false;
        }
            return true;
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function renderForm()
    {
        $field_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings bloc filter best sellers'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                        'label' => $this->l('Active Bloc Categories'),
                        'name' => 'ACTIVATE_BLOC_CATEGORIES',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Non')
                            )
                        )
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Products categories to display'),
                    'name' => 'PRODUCT_NUMBER_CATEGORIES'
                ],
                [
                    'type' => 'switch',
                        'label' => $this->l('Active Bloc Marque'),
                        'name' => 'ACTIVATE_BLOC_MARQUE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Non')
                            )
                        )
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Products marques to display'),
                    'name' => 'PRODUCT_NUMBER_MARQUE'
                ]
            ],
            'submit' => [
                'title' => $this->l('save'),
                'class' => 'btn btn-primary',
                'name' => 'saving'
            ]
        ];

        $helper = new HelperForm();
        $helper->module  = $this;
        $helper->name_controller = $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['ACTIVATE_BLOC_CATEGORIES'] = Configuration::get('ACTIVATE_BLOC_CATEGORIES');
        $helper->fields_value['PRODUCT_NUMBER_CATEGORIES'] = Configuration::get('PRODUCT_NUMBER_CATEGORIES');
        $helper->fields_value['ACTIVATE_BLOC_MARQUE'] = Configuration::get('ACTIVATE_BLOC_MARQUE');
        $helper->fields_value['PRODUCT_NUMBER_MARQUE'] = Configuration::get('PRODUCT_NUMBER_MARQUE');

        return $helper->generateForm($field_form);
    }

    public function postProcess()
    {
        if(Tools::isSubmit('saving'))
        {
            if(Validate::isBool(Tools::getValue('ACTIVATE_BLOC_CATEGORIES')) ||
            Validate::isBool(Tools::getValue('ACTIVATE_BLOC_MARQUE'))
            )
            {
                Configuration::updateValue('ACTIVATE_BLOC_CATEGORIES',Tools::getValue('ACTIVATE_BLOC_CATEGORIES'));
                Configuration::updateValue('ACTIVATE_BLOC_MARQUE',Tools::getValue('ACTIVATE_BLOC_MARQUE'));
                Configuration::updateValue('PRODUCT_NUMBER_CATEGORIES',Tools::getValue('PRODUCT_NUMBER_CATEGORIES'));
                Configuration::updateValue('PRODUCT_NUMBER_MARQUE',Tools::getValue('PRODUCT_NUMBER_MARQUE'));
                return $this->displayConfirmation('Les champs ont bien été enregistré');
            }

        }
    }


    protected function getBestSellers()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return false;
        }

        // We will use the default core search provider to get the products
        $searchProvider = new BestSalesProductSearchProvider(
            $this->context->getTranslator()
        );

        $context = new ProductSearchContext($this->context);
        if(Configuration::get('ACTIVATE_BLOC_CATEGORIES') == 1)
        {
            // Build the search query
            $query = new ProductSearchQuery();
            $query
                ->setResultsPerPage((int) Configuration::get('PRODUCT_NUMBER_CATEGORIES'))
                ->setPage(1)
                ->setSortOrder(new SortOrder('product', 'sales', 'desc'))
            ;

            $result = $searchProvider->runQuery(
                $context,
                $query
            );
        }
        else if(Configuration::get('ACTIVATE_BLOC_MARQUE') ==1)
        {
            // Build the search query
            $query = new ProductSearchQuery();
            $query
                ->setResultsPerPage((int) Configuration::get('PRODUCT_NUMBER_MARQUE'))
                ->setPage(1)
                ->setSortOrder(new SortOrder('product', 'sales', 'desc'))
            ;

            $result = $searchProvider->runQuery(
                $context,
                $query
            );
        }
        

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
            $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );
        } else {
            $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );
        }

        $products_for_template = [];

        foreach ($result->getProducts() as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }

    public function renderWidget($hookName, array $configuration)
    {
        $variables = $this->getWidgetVariables($hookName,$configuration);

        if(empty($variables))
        {
            return false;
        }

        $this->smarty->assign($variables);

        return $this->fetch('module:blockfilterproductbestsellers/views/templates/hook/displayHeaderCategory.tpl');
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        
        
        $datas = $this->getBestSellers();

        $tabCategory = [];

        $tabManufaturer = [];

        $url = explode('/',$_SERVER['REQUEST_URI']);
        $url2 = explode('-',end($url));

        if(!empty($datas))
        {
            if($this->context->controller->php_self == 'category' && Configuration::get('ACTIVATE_BLOC_CATEGORIES') == 1)
            {
                foreach ($datas as $data) {
                    if($url2[0] == $data['id_category_default'])
                    {
                        $tabCategory[] = $data;
                    }
                }
                return [
                    'products' => $tabCategory,
                    'allBestSellers' => Context::getContext()->link->getPageLink('best-sales'),
                ];
            }

            if($this->context->controller->php_self == 'manufacturer' && Configuration::get('ACTIVATE_BLOC_MARQUE') == 1)
            {
                foreach ($datas as $data) {
                    if($url2[0] == $data['id_manufacturer'])
                    {
                        $tabManufaturer[] = $data;
                    }
                }

                return [
                    'products' => $tabManufaturer,
                    'allBestSellers' => Context::getContext()->link->getPageLink('best-sales'),
                ];
            }
        }

        return false;
    }
}