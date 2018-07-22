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
import axios from 'axios';
import xss from 'xss';
import styles from '../css/qrcode.css';

const PENDING = 1;
const SUCCESS = 2;
const REFRESH = 3;

export default class QrCode {
  constructor(target, title = '', center = true) {
    if (typeof target === 'string') {
      this.target = document.querySelector(target);
    } else {
      this.target = target;
    }
    this.title = title;
    this.suffix = `mol${Math.random().toString(36).substring(7)}`;
    this.center = center;

    this.init();
    this.grabAmount().then(this.initQrImage);
    window.addEventListener('resize', QrCode.throttle(() => {
      QrCode.checkWindowSize();
    }, 200));
  }

  init = () => {
    const elem = this.target;
    if (elem == null) {
      return;
    }
    elem.style.width = '100%';
    elem.style.height = '280px';
    let content = `<div id="mollie-spinner-${this.suffix}" class="${styles.spinner}" style="height: 100px">
    <div class="${styles.bounce1}"></div>
    <div class="${styles.bounce2}"></div>
    <div></div>
  </div>
  <div id="mollie-qr-image-container-${this.suffix}" style="text-align: ${this.center ? 'center' : 'left'}">`;
    if (this.title) {
      content += `<span id="mollie-qr-title-${this.suffix}" style="font-size: 20px">${xss(this.title)}</span>`;
    }
    content += `<img id="mollie-qr-image-${this.suffix}" width="320" height="320" style="height: 240px; width: 240px; ${this.center ? 'margin: 0 auto; ' : ''} visibility: hidden">
  </div>`;
    elem.innerHTML = content;
  }

  static throttle(callback, delay) {
    let isThrottled = false, args, context;

    function wrapper() {
      if (isThrottled) {
        args = arguments;
        context = this;
        return;
      }

      isThrottled = true;
      callback.apply(this, arguments);

      setTimeout(() => {
        isThrottled = false;
        if (args) {
          wrapper.apply(context, args);
          args = context = null;
        }
      }, delay);
    }

    return wrapper;
  }

  static clearCache = () => {
    for (let key in window.localStorage) {
      if (key.indexOf('mollieqrcache') > -1) {
        window.localStorage.removeItem(key);
      }
    }
  };

  static checkWindowSize() {
    const elem = this.target;
    if (elem) {
      if (window.innerWidth > 800 && window.innerHeight > 860) {
        elem.style.display = 'block';
      } else {
        elem.style.display = 'none';
      }
    }
  }

  pollStatus = (idTransaction) => {
    setTimeout(async () => {
      try {
        const { data } = await axios.get(`${window.MollieModule.urls.qrCodeStatus}&transaction_id=${idTransaction}`);
        if (data.status === SUCCESS) {
          QrCode.clearCache();

          // Never redirect to a different domain
          const a = document.createElement('A');
          a.href = data.href;
          if (a.hostname === window.location.hostname) {
            window.location.href = data.href;
          }
        } else if (data.status === REFRESH) {
          QrCode.clearCache();
          this.grabNewUrl().then();
        } else {
          this.pollStatus(idTransaction);
        }
      } catch (e) {
        this.pollStatus(idTransaction);
      }
    }, 5000);
  };

  grabAmount = async () => {
    try {
      const  { data: { amount } } = await axios.get(window.MollieModule.urls.cartAmount);
      return amount;
    } catch (e) {
      console.error(e);
    }
  };

  grabNewUrl = async () => {
    try {
      const { data } = await axios.get(window.MollieModule.urls.qrCodeNew);
      window.localStorage.setItem('mollieqrcache-' + data.expires + '-' + data.amount, JSON.stringify({
        url: data.href,
        idTransaction: data.idTransaction,
      }));
      // Preload an image and check if it loads, if not, hide the qr block
      const img = new Image();
      img.onload = () => {
        if (img.src && img.width) {
          const elem = document.getElementById(`mollie-qr-image-${this.suffix}`);
          elem.src = data.href;
          elem.style.display = 'block';
          document.getElementById(`mollie-spinner-${this.suffix}`).style.display = 'none';
          document.getElementById(`mollie-qr-image-${this.suffix}`).style.visibility = 'visible';
          this.pollStatus(data.idTransaction);
        } else {
          this.target.style.display = 'none';
        }
      };
      img.onerror = () => {
        this.target.style.display = 'none';
      };
      img.src = data.href;
    } catch (e) {
      console.error(e);
    }
  };

  initQrImage = (amount) => {
    const elem = document.getElementById(`mollie-qr-image-${this.suffix}`);
    if (elem == null) {
      return;
    }
    elem.style.display = 'none';

    let url = null;
    let idTransaction = null;
    if (typeof window.localStorage !== 'undefined') {
      for (let key in window.localStorage) {
        if (key.indexOf('mollieqrcache') > -1) {
          const cacheInfo = key.split('-');
          if (cacheInfo[1] > (+new Date() + 60 * 1000) && parseInt(cacheInfo[2], 10) === amount) {
            const item = JSON.parse(window.localStorage.getItem(key));
            const a = document.createElement('A');
            a.href = item.url;
            if (!/\.ideal\.nl$/i.test(a.hostname) || a.protocol !== 'https:') {
              window.localStorage.removeItem(key);
              continue;
            }
            // Valid
            url = item.url;
            idTransaction = item.idTransaction;
            break;
          } else {
            window.localStorage.removeItem(key);
          }
        }
      }

      if (url && idTransaction) {
        const img = new Image();
        img.onload = () => {
          if (img.src && img.width) {
            elem.src = url;
            elem.style.display = 'block';
            document.getElementById(`mollie-spinner-${this.suffix}`).style.display = 'none';
            document.getElementById(`mollie-qr-image-${this.suffix}`).style.visibility = 'visible';
            this.pollStatus(idTransaction);
          } else {
            this.target.style.display = 'none';
          }
        };
        img.onerror = () => {
          this.target.style.display = 'none';
        };
        img.src = url;
      } else {
        this.grabNewUrl().then();
      }
    }
  };
}
