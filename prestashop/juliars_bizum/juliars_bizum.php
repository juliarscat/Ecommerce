<?php
/**
*  @author JuliaRS <contacte@juliars.cat>
*  
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Symfony\Component\Translation\TranslatorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class juliars_bizum extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    public $checkName;
    public $movile;
    public $extra_mail_vars;

    private $translator;

    public function __construct()
    {
        $this->name = 'juliars_bizum';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Juliars';
        $this->controllers = array('payment', 'validation');
       
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array('BYZUM_NAME', 'BYZUM_MOVILE'));
        if (isset($config['BYZUM_NAME'])) {
            $this->checkName = $config['BYZUM_NAME'];
        }
        if (isset($config['BYZUM_MOVILE'])) {
            $this->movile = $config['BYZUM_MOVILE'];
        }

        if (isset($config['PS_OS_BIZUM'])) {
            $this->byzumos = $config['PS_OS_BIZUM'];
        }

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Pago por Bizum');
        $this->description = $this->l('Con nuestro módulo podrás aceptar pagos con Bizum en PS 1.7');
        $this->confirmUninstall = $this->l('¿Estás seguro de borrar este módulo?');
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);

        if ((!isset($this->checkName) || !isset($this->movile) || empty($this->checkName) || empty($this->movile))) {
            $this->warning = $this->l('The "Name of" and "Movile" fields must be configured before using this module.');
        }
        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }

        $this->extra_mail_vars = array(
                                    '{check_name}' => Configuration::get('BYZUM_NAME'),
                                    '{check_movile}' => Configuration::get('BYZUM_MOVILE'),
                                    '{check_movile_html}' => Tools::nl2br(Configuration::get('BYZUM_MOVILE'))
                                );
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
            && $this->addOrderState($this->l('Bizum')) 
        ;
    }

    public function uninstall()
    {
        return Configuration::deleteByName('BYZUM_NAME')
            && Configuration::deleteByName('BYZUM_MOVILE')
            && parent::uninstall()
        ;
    }

    public function addOrderState($name)
    {
        $state_exist = false;
        $states = OrderState::getOrderStates((int)$this->context->language->id);
 
        // check if order state exist
        foreach ($states as $state) {
            if (in_array($name, $state)) {
                $state_exist = true;
                break;
            }
        }
 
        // If the state does not exist, we create it.
        if (!$state_exist) {
            // create new order state
            $order_state = new OrderState();
            $order_state->color = '#39aaaa';
            $order_state->send_email = true;
          //  $order_state->module_name = 'En espera de Bizum';
            $order_state->template = 'bizum';
            $order_state->name = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
                $order_state->name[ $language['id_lang'] ] = $name;
 
            // Update object
            $order_state->add();
        }

        if (!copy( _PS_MODULE_DIR_ . 'juliars_bizum/mails/es/bizum.html', _PS_MAIL_DIR_.'/es/bizum.html' )) {
            echo "Error al copiar mail html...\n";
        }

        if (!copy( _PS_MODULE_DIR_ . 'juliars_bizum/mails/es/bizum.txt', _PS_MAIL_DIR_.'/es/bizum.txt' )) {
            echo "Error al copiar mail html...\n";
        }
       
        return true;
    }


    public static function getOrderStatesbyname($id_lang, $name='Bizum')
    {
        $cache_id = 'OrderState::getOrderStates_' . (int) $id_lang;
        if (!Cache::isStored($cache_id)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'order_state` os
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $id_lang . ')
            WHERE deleted = 0 and osl.name = \''.$name.'\' 
            ORDER BY `name` ASC');
            Cache::store($cache_id, $result);

            return $result[0]['id_order_state'];
        }

        return Cache::retrieve($cache_id);
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('BYZUM_NAME')) {
                $this->_postErrors[] = $this->l('The "Company" field is required.');
            } elseif (!Tools::getValue('BYZUM_MOVILE')) {
                $this->_postErrors[] = $this->l('The "Movile" field is required.');
            }
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('BYZUM_NAME', Tools::getValue('BYZUM_NAME'));
            Configuration::updateValue('BYZUM_MOVILE', Tools::getValue('BYZUM_MOVILE'));

            $idorderstate = $this->getOrderStatesbyname((int)$this->context->language->id);
            Configuration::updateValue('PS_OS_BYZUM', $idorderstate);


        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    private function _displayCheck()
    {
        return $this->display(__FILE__, './views/templates/hook/infos.tpl');
    }

    public function getContent()
    {
        $this->_html = '';

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        }

        $this->_html .= $this->_displayCheck();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->smarty->assign(
            $this->getTemplateVars()
        );
     
        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
                ->setCallToActionText($this->l('Pago con Bizum'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                ->setAdditionalInformation($this->fetch('module:juliars_bizum/views/templates/front/payment_infos.tpl'));

        return [$newOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        //$idorderstate = $this->getOrderStatesbyname((int)$this->context->language->id);
        $state = $params['order']->getCurrentState();
        if (in_array($state, array( Configuration::get('PS_OS_BYZUM'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')))) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice(
                    $params['order']->getOrdersTotalPaid(),
                    new Currency($params['order']->id_currency),
                    false
                ),
                'shop_name' => $this->context->shop->name,
                'checkName' => $this->checkName,
                'checkMovile' => Tools::nl2br($this->movile),
                'status' => 'ok',
                'id_order' => $params['order']->id
            ));
            if (isset($params['order']->reference) && !empty($params['order']->reference)) {
                $this->smarty->assign('reference', $params['order']->reference);
            }
        } else {
            $this->smarty->assign('status', 'failed');
        }
        return $this->fetch('module:juliars_bizum/views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency((int)($cart->id_currency));
        $currencies_module = $this->getCurrency((int)$cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Detalles de contacto'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Nombre de tu empresa'),
                        'name' => 'BYZUM_NAME',
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Mobile'),
                        'desc' => $this->l('Teléfono al que debe de ser enviado el pago móvil.'),
                        'name' => 'BYZUM_MOVILE',
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
        );

        $this->fields_form = array();

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'BYZUM_NAME' => Tools::getValue('BYZUM_NAME', Configuration::get('BYZUM_NAME')),
            'BYZUM_MOVILE' => Tools::getValue('BYZUM_MOVILE', Configuration::get('BYZUM_MOVILE')),
        );
    }

    public function getTemplateVars()
    {
        $cart = $this->context->cart;
        $total = $this->trans(
            '%amount% (impuestos incluidos)',
            array(
                '%amount%' => Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)),
            ),
            'Modules.Bizum.Admin'
        );

        $checkOrder = Configuration::get('BYZUM_NAME');
        if (!$checkOrder) {
            $checkOrder = '___________';
        }

        $checkMovile = Tools::nl2br(Configuration::get('BYZUM_MOVILE'));
        if (!$checkMovile) {
            $checkMovile = '___________';
        }

        return [
            'checkTotal' => $total,
            'checkOrder' => $checkOrder,
            'checkMovile' => $checkMovile,
        ];
    }
}

