/*
 * Copyright (C) 2024 Anti-DDoS Coalitie Netherlands (ADC-NL)
 *
 * This file is part of the DDoS gameboard.
 *
 * DDoS gameboard is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * DDoS gameboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see @link https://www.gnu.org/licenses/.
 *
 */

// This function is looping through the language strings in the window.lang object to the Vue scheduler
// The stings from window.lang reside in /lang/lang.yaml

window.
    l = function (stringname) {
    //loop through the top level
    if (window.lang) {
        for (let key in window.lang) {
            if (key === window.gameboard_locale) {
                //loop through the children
                for (langname in window.lang[key]) {
                    if (langname === stringname) {
                        return window.lang[key][langname];
                    }
                }
            } else {
                console.error('set language keyname: "' + stringname + '" not found in lang.yaml');
            }
        }
    } else {
        console.error('window.lang');
    }
}
