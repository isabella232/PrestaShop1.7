/**
 * Copyright (c) 2012-2018, Mollie B.V.
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
 */
import 'babel-polyfill';
import swal from 'sweetalert';
import xss from 'xss';
import axios from 'axios';

const showError = (message) => {
  swal({
    icon: 'error',
    title: _.get(document, 'documentElement.lang', 'en') === 'nl' ? 'Fout' : 'Error',
    text: xss(message),
  }).then();
};

const handleClick = async (button, config, translations) => {
  const steps = [
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToConnect,
    },
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToUnzip,
    },
    {
      action: 'downloadUpdate',
      defaultError: translations.unableToConnect,
    },
  ];

  for (step of steps) {
    try {
      const { data } = await axios.get(`${config.endpoint}&action=${step.action}`);
      if (!_.get(data, 'success')) {
        showError(_.get(data, 'message', step.defaultError));
      }
    } catch (e) {
      console.error(e);
      showError(step.defaultError);
    }
  }

  swal({
    icon: 'success',
    text: translations.updated
  }).then();
};

const init = (button, config, translations) => {
  button.onclick = () => handleClick(button, config, translations);
};

export default init;

