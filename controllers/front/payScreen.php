<?php
/**
 * Copyright (c) 2012-2020, Mollie B.V.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @author     Mollie B.V. <info@mollie.nl>
 * @copyright  Mollie B.V.
 * @license    Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @category   Mollie
 * @package    Mollie
 * @link       https://www.mollie.nl
 * @codingStandardsIgnoreStart
 */

use _PhpScoper5ece82d7231e4\Mollie\Api\MollieApiClient;
use _PhpScoper5ece82d7231e4\Mollie\Api\Types\PaymentMethod;

class MolliePayScreenModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {
        $method = Tools::getValue('method');
        $cardToken = Tools::getValue('mollieCardToken' . $method);

        $validateUrl = Context::getContext()->link->getModuleLink(
            'mollie',
            'payment',
            array('method' => PaymentMethod::CREDITCARD, 'rand' => time(), 'cardToken' => $cardToken),
            true
        );

        Tools::redirect($validateUrl);
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign([
            'mollieIFrameJS' => 'https://js.mollie.com/v1/mollie.js',
            'price' => $this->context->cart->getOrderTotal(),
            'priceSign' => $this->context->currency->getSign()
        ]);
        $this->setTemplate('module:mollie/views/templates/' . 'front/mollie_iframe.tpl');
    }

    public function setMedia()
    {
        Media::addJsDef([
            'profileId' => Configuration::get(Mollie\Config\Config::MOLLIE_PROFILE_ID),
        ]);
        $this->addJS("{$this->module->getPathUri()}views/js/front/mollie_iframe.js");
        $this->addCSS("{$this->module->getPathUri()}views/css/mollie_iframe.css");

        return parent::setMedia(); // TODO: Change the autogenerated stub
    }
}
