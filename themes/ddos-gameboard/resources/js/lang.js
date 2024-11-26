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