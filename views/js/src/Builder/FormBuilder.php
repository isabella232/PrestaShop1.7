<?php

NameSpace Mollie\Builder;

use Configuration;
use HelperForm;
use Mollie;
use Mollie\Config\Config;
use Tools;

class FormBuilder
{

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    protected function buildSettingsForm()
    {
        $isApiKeyProvided = Configuration::get(Config::MOLLIE_API_KEY);

        $inputs = $this->getAccountSettingsSection($isApiKeyProvided);

        if ($isApiKeyProvided) {
            $inputs = array_merge($inputs, $this->getAdvancedSettingsSection());
        }

        $fields = [
            'form' => [
                'tabs' => $this->getSettingTabs($isApiKeyProvided),
                'input' => $inputs,
                'submit' => [
                    'title' => $this->module->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->module->getTable();
        $helper->module = $this;
        $helper->default_form_language = $this->module->getContext()->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->module->getIdentifier();
        $helper->submit_action = 'submitmollie';
        $helper->currentIndex = $this->module->getContext()->link->getAdminLink('AdminModules', false)
            . "&configure={$this->module->name}&tab_module={$this->module->tab}&module_name={$this->module->name}";
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->module->getContext()->controller->getLanguages(),
            'id_language' => $this->module->getContext()->language->id,
        ];

        return $helper->generateForm([$fields]);
    }

    protected function getAccountSettingsSection($isApiKeyProvided)
    {
        /** @var \Mollie\Service\ApiService $apiService */
        $apiService = $this->getContainer(\Mollie\Service\ApiService::class);

        $generalSettings = 'general_settings';
        if ($isApiKeyProvided) {
            $input = [
                [
                    'type' => 'text',
                    'label' => $this->module->l('API Key'),
                    'tab' => $generalSettings,
                    'desc' => \Mollie\Utility\TagsUtility::ppTags(
                        $this->module->l('You can find your API key in your [1]Mollie Profile[/1]; it starts with test or live.'),
                        [$this->display(__FILE__, 'views/templates/admin/profile.tpl')]
                    ),
                    'name' => Config::MOLLIE_API_KEY,
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                ]
            ];
        } else {
            $input = [
                [
                    'type' => 'mollie-switch',
                    'label' => $this->module->l('Do you already have a Mollie account?'),
                    'name' => Config::MOLLIE_ACCOUNT_SWITCH,
                    'tab' => $generalSettings,
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ],
                    ],
                    'desc' => $this->context->smarty->fetch(
                        $this->getLocalPath() . 'views/templates/admin/create_new_account_link.tpl'
                    ),
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('API Key'),
                    'tab' => $generalSettings,
                    'desc' => \Mollie\Utility\TagsUtility::ppTags(
                        $this->module->l('You can find your API key in your [1]Mollie Profile[/1]; it starts with test or live.'),
                        [$this->display(__FILE__, 'views/templates/admin/profile.tpl')]
                    ),
                    'name' => Config::MOLLIE_API_KEY,
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                ]
            ];
        }
        if ($isApiKeyProvided) {
            $input[] = [
                'type' => 'switch',
                'label' => $this->module->l('Use IFrame for credit card'),
                'tab' => $generalSettings,
                'name' => Config::MOLLIE_IFRAME,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => true,
                        'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => false,
                        'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ];

            $input[] = [
                'type' => 'text',
                'label' => $this->module->l('Profile ID'),
                'tab' => $generalSettings,
                'desc' => \Mollie\Utility\TagsUtility::ppTags(
                    $this->module->l('You can find your API key in your [1]Mollie Profile[/1];'),
                    [$this->display(__FILE__, 'views/templates/admin/profile.tpl')]
                ),
                'name' => Config::MOLLIE_PROFILE_ID,
                'required' => true,
                'class' => 'fixed-width-xxl',
            ];

            $input = array_merge($input, [
                    [
                        'type' => 'mollie-h3',
                        'tab' => $generalSettings,
                        'name' => '',
                        'title' => $this->module->l('Orders API'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->module->l('Issuer list'),
                        'tab' => $generalSettings,
                        'desc' => $this->module->l('Some payment methods (eg. iDEAL) have an issuer list. This setting specifies where it is shown.'),
                        'name' => Config::MOLLIE_ISSUERS,
                        'options' => [
                            'query' => [
                                [
                                    'id' => Config::ISSUERS_ON_CLICK,
                                    'name' => $this->module->l('On click'),
                                ],
                                [
                                    'id' => Config::ISSUERS_PAYMENT_PAGE,
                                    'name' => $this->module->l('Payment page'),
                                ],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ]
            );
            $input[] = [
                'type' => 'mollie-h2',
                'tab' => $generalSettings,
                'name' => '',
                'title' => $this->module->l('Payment methods'),
            ];

            $input[] = [
                'type' => 'mollie-methods',
                'name' => Config::METHODS_CONFIG,
                'paymentMethods' => $apiService->getMethodsForConfig($this->api, $this->getPathUri()),
                'countries' => $this->getActiveCountriesList(),
                'tab' => $generalSettings,
            ];
        }

        return $input;
    }

    protected function getAdvancedSettingsSection()
    {
        $advancedSettings = 'advanced_settings';
        $input = [];
        $orderStatuses = [];
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->context->language->id));
        $input[] = [
            'type' => 'select',
            'label' => $this->module->l('Send locale for payment screen'),
            'tab' => $advancedSettings,
            'desc' => \Mollie\Utility\TagsUtility::ppTags(
                $this->module->l('Should the plugin send the current webshop [1]locale[/1] to Mollie. Mollie payment screens will be in the same language as your webshop. Mollie can also detect the language based on the user\'s browser language.'),
                [$this->display(__FILE__, 'views/templates/admin/locale_wiki.tpl')]
            ),
            'name' => Config::MOLLIE_PAYMENTSCREEN_LOCALE,
            'options' => [
                'query' => [
                    [
                        'id' => Config::PAYMENTSCREEN_LOCALE_BROWSER_LOCALE,
                        'name' => $this->module->l('Do not send locale using browser language'),
                    ],
                    [
                        'id' => Config::PAYMENTSCREEN_LOCALE_SEND_WEBSITE_LOCALE,
                        'name' => $this->module->l('Send locale for payment screen'),
                    ],
                ],
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $lang = Context::getContext()->language->id;
        $messageStatus = $this->module->l('Status for %s payments');
        $descriptionStatus = $this->module->l('`%s` payments get status `%s`');
        $messageMail = $this->module->l('Send mails when %s');
        $descriptionMail = $this->module->l('Send mails when transaction status becomes %s?');
        $allStatuses = array_merge([['id_order_state' => 0, 'name' => $this->module->l('Skip this status'), 'color' => '#565656']], OrderState::getOrderStates($lang));
        $statuses = [];
        foreach (Config::getStatuses() as $name => $val) {
            if ($name === \Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED) {
                continue;
            }

            $val = (int)$val;
            if ($val) {
                $desc = Tools::strtolower(
                    sprintf(
                        $descriptionStatus,
                        $this->module->lang($name),
                        Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                            'SELECT `name`
                            FROM `' . _DB_PREFIX_ . 'order_state_lang`
                            WHERE `id_order_state` = ' . (int)$val . '
                            AND `id_lang` = ' . (int)$lang
                        )
                    )
                );
            } else {
                $desc = sprintf($this->module->l('`%s` payments do not get a status'), $this->module->lang($name));
            }
            $statuses[] = [
                'name' => $name,
                'key' => @constant('Config::MOLLIE_STATUS_' . Tools::strtoupper($name)),
                'value' => $val,
                'description' => $desc,
                'message' => sprintf($messageStatus, $this->module->lang($name)),
                'key_mail' => @constant('Config::MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
                'value_mail' => Configuration::get('MOLLIE_MAIL_WHEN_' . Tools::strtoupper($name)),
                'description_mail' => sprintf($descriptionMail, $this->module->lang($name)),
                'message_mail' => sprintf($messageMail, $this->module->lang($name)),
            ];
        }
        $input[] = [
            'type' => 'mollie-h2',
            'name' => '',
            'tab' => $advancedSettings,
            'title' => $this->module->l('Order statuses'),
        ];

        foreach (array_filter($statuses, function ($status) {
            return in_array($status['name'], [
                \Mollie\Api\Types\PaymentStatus::STATUS_PAID,
                \Mollie\Api\Types\PaymentStatus::STATUS_AUTHORIZED,
                \Mollie\Api\Types\PaymentStatus::STATUS_CANCELED,
                \Mollie\Api\Types\PaymentStatus::STATUS_EXPIRED,
                \Mollie\Api\Types\RefundStatus::STATUS_REFUNDED,
                \Mollie\Api\Types\PaymentStatus::STATUS_OPEN,
                'partial_refund',
            ]);
        }) as $status) {
            if (!in_array($status['name'], ['paid', 'partial_refund'])) {
                $input[] = [
                    'type' => 'switch',
                    'label' => $status['message_mail'],
                    'tab' => $advancedSettings,
                    'name' => $status['key_mail'],
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ],
                    ],
                ];
            }
            $input[] = [
                'type' => 'select',
                'label' => $status['message'],
                'tab' => $advancedSettings,
                'desc' => $status['description'],
                'name' => $status['key'],
                'options' => [
                    'query' => $allStatuses,
                    'id' => 'id_order_state',
                    'name' => 'name',
                ],
            ];
        }
        $input = array_merge($input, [
            [
                'type' => 'mollie-h2',
                'name' => '',
                'tab' => $advancedSettings,
                'title' => $this->module->l('Visual Settings'),
            ],
            [
                'type' => 'select',
                'label' => $this->module->l('Images'),
                'tab' => $advancedSettings,
                'desc' => $this->module->l('Show big, normal or no payment method logos on checkout.'),
                'name' => Config::MOLLIE_IMAGES,
                'options' => [
                    'query' => [
                        [
                            'id' => Config::LOGOS_HIDE,
                            'name' => $this->module->l('hide'),
                        ],
                        [
                            'id' => Config::LOGOS_NORMAL,
                            'name' => $this->module->l('normal'),
                        ],
                        [
                            'id' => Config::LOGOS_BIG,
                            'name' => $this->module->l('big'),
                        ],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],

            [
                'type' => 'text',
                'label' => $this->module->l('CSS file'),
                'tab' => $advancedSettings,
                'desc' => \Mollie\Utility\TagsUtility::ppTags(
                    $this->module->l('Leave empty for default stylesheet. Should include file path when set. Hint: You can use [1]{BASE}[/1], [1]{THEME}[/1], [1]{CSS}[/1], [1]{MOBILE}[/1], [1]{MOBILE_CSS}[/1] and [1]{OVERRIDE}[/1] for easy folder mapping.'),
                    [$this->display(__FILE__, 'views/templates/front/kbd.tpl')]
                ),
                'name' => Config::MOLLIE_CSS,
                'class' => 'long-text',
            ],
        ]);
        $input[] = [
            'type' => 'mollie-carriers',
            'label' => $this->module->l('Shipment information'),
            'tab' => $advancedSettings,
            'name' => Config::MOLLIE_TRACKING_URLS,
            'depends' => Config::MOLLIE_API,
            'depends_value' => Config::MOLLIE_ORDERS_API,
        ];
        $input[] = [
            'type' => 'mollie-carrier-switch',
            'label' => $this->module->l('Automatically ship on marked statuses'),
            'tab' => $advancedSettings,
            'name' => Config::MOLLIE_AUTO_SHIP_MAIN,
            'desc' => $this->module->l('Enabling this feature will automatically send shipment information when an order gets marked status'),
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                    'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                    'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                ],
            ],
            'depends' => Config::MOLLIE_API,
            'depends_value' => Config::MOLLIE_ORDERS_API,
        ];
        $input[] = [
            'type' => 'checkbox',
            'label' => $this->module->l('Automatically ship when one of these statuses is reached'),
            'tab' => $advancedSettings,
            'desc' =>
                $this->module->l('If an order reaches one of these statuses the module will automatically send shipment information'),
            'name' => Config::MOLLIE_AUTO_SHIP_STATUSES,
            'multiple' => true,
            'values' => [
                'query' => $orderStatuses,
                'id' => 'id_order_state',
                'name' => 'name',
            ],
            'expand' => (count($orderStatuses) > 10) ? [
                'print_total' => count($orderStatuses),
                'default' => 'show',
                'show' => ['text' => $this->module->l('Show'), 'icon' => 'plus-sign-alt'],
                'hide' => ['text' => $this->module->l('Hide'), 'icon' => 'minus-sign-alt'],
            ] : null,
            'depends' => Config::MOLLIE_API,
            'depends_value' => Config::MOLLIE_ORDERS_API,
        ];
        $orderStatuses = [
            [
                'name' => $this->module->l('Disable this status'),
                'id_order_state' => '0',
            ],
        ];
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->context->language->id));

        for ($i = 0; $i < count($orderStatuses); $i++) {
            $orderStatuses[$i]['name'] = $orderStatuses[$i]['id_order_state'] . ' - ' . $orderStatuses[$i]['name'];
        }

        $this->aasort($orderStatuses, 'id_order_state');

        $this->context->smarty->assign([
            'logs' => $this->context->link->getAdminLink('AdminLogs')
        ]);
        $input = array_merge(
            $input,
            [
                [
                    'type' => 'mollie-h2',
                    'name' => '',
                    'title' => $this->module->l('Debug level'),
                    'tab' => $advancedSettings,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Display errors'),
                    'tab' => $advancedSettings,
                    'name' => Config::MOLLIE_DISPLAY_ERRORS,
                    'desc' => $this->module->l('Enabling this feature will display error messages (if any) on the front page. Use for debug purposes only!'),
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => \Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => \Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Log level'),
                    'tab' => $advancedSettings,
                    'desc' => \Mollie\Utility\TagsUtility::ppTags(
                        $this->module->l('Recommended level: Errors. Set to Everything to monitor incoming webhook requests. [1]View logs.[/1]'),
                        [
                            $this->display(__FILE__, 'views/templates/admin/view_logs.tpl')
                        ]
                    ),
                    'name' => Config::MOLLIE_DEBUG_LOG,
                    'options' => [
                        'query' => [
                            [
                                'id' => Config::DEBUG_LOG_NONE,
                                'name' => $this->module->l('Nothing'),
                            ],
                            [
                                'id' => Config::DEBUG_LOG_ERRORS,
                                'name' => $this->module->l('Errors'),
                            ],
                            [
                                'id' => Config::DEBUG_LOG_ALL,
                                'name' => $this->module->l('Everything'),
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
            ]
        );
        return $input;
    }

    protected function getSettingTabs($isApiKeyProvided)
    {
        $tabs = [
            'general_settings' => $this->module->l('General settings'),
        ];

        if ($isApiKeyProvided) {
            $tabs['advanced_settings'] = $this->module->l('Advanced settings');
        }

        return $tabs;
    }
}