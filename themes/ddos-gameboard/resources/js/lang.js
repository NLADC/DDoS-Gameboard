//This function is for transferring the language strings in /json/lang.json to the Vue components
window.l = function (stringname) {
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
                console.log('set lang not found in lang.json');
            }
        }
    } else {
        console.log('lang.json empty');
    }
}