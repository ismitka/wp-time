/**
 The MIT License

 Copyright 2023 Ivan Smitka <ivan at stimulus dot cz>.

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.
 */

import dateFormat from "dateformat";
import {parseExpression} from "cron-parser";

export namespace WpTime {
    interface Cfg {
        on: string,
        off: string
    }

    /**
     * Update Time on elements with attribute data-time
     */
    const updateTime = (): void => {
        const now = new Date();
        const elements = document.querySelectorAll<HTMLElement>('[data-time]');
        //console.log(now, elements);
        elements.forEach((element) => {
            let format = element.dataset.time;
            if (format === undefined || format === "") {
                format = "HH:MM";
            }
            if (format) {
                element.textContent = dateFormat(now, format);
            }
        });
    }

    const updateElementVisibility = () => {
        const elements = document.querySelectorAll<HTMLElement>('[data-on-time]');
        elements.forEach((element) => {
            if(element.dataset.onTime) {
                const cfg: Cfg = JSON.parse(element.dataset.onTime);
                const on = parseExpression(cfg.on).next();
                const off = parseExpression(cfg.off).next();
                if(on.getTime() < off.getTime()) {
                    element.classList.remove("active");
                } else {
                    element.classList.add("active");
                }
            }
        });
    }

    export const init = () => {
        updateTime();
        updateElementVisibility();
        setInterval(() => {
            updateTime();
            updateElementVisibility();
        }, 1000);
    }
}

WpTime.init();