// Root is needed to set css vars to
const root = document.documentElement;

(function (global) {
    global.foo = 'bar';
}).call(this, typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : typeof window !== 'undefined' ? window : {})


/* Function to update the game-header height in the css,
 * The party-header is sticky and needs to now from which point to stick top the top of the page
 */
window.updateGameHeaderHeight = function () {
    try {
        // Calculate the height the GameHeader wants to naturaly
        let heightheaderinner = (document.querySelector('#game-header .game-header-inner').clientHeight);
        let usermenu = (document.querySelector('#game-header #usermenu').clientHeight);
        let height = Math.max(heightheaderinner, usermenu);
        // Letting the css now at which height needs to be dealt with by overriding css var
        root.style.setProperty('--gameheader-height', height + "px");
    } catch (err) {
        console.log('responsive.js: ' + err.message);
    }
}

/** Function to update the partyheader-height
 * this is usefull for the sticky action-header
 * It needs to now from wich point it needs to be sticky in the page
 */
window.updateActionHeaderHeight = function () {
    try {
        if (document.querySelector('#parties-board .party-header h2.header')) {
            // Getting al the different heights from the party headers
            const partyheaders = document.querySelectorAll('#parties-board .party-header h2.header');
            let heights = [];
            for (let i = 0; i < partyheaders.length; i++) {
                let height = partyheaders[i].getBoundingClientRect().height;
                heights.push(height);
                // When at the last party header
                if ((i + 1) == (partyheaders.length)) {
                    // Getting the highest height
                    const highestheight = Math.max(...heights);
                    if (typeof highestheight === 'number') {
                        // Letting the css now at which height needs to be dealt with by overriding css var
                        root.style.setProperty('--partyheader-height', highestheight + "px");
                    } else {
                        // fallback when highestheight is "NaNaN.." or something else
                        root.style.setProperty('--partyheader-height', 64 + "px");
                    }
                }
            }
        }
    } catch (err) {
        console.log(err.message);
    }
}

// Recalculate when page is loaded
window.onload = function () {
    window.updateGameHeaderHeight();
    window.updateActionHeaderHeight();
}

// When resizing window recalculate
window.addEventListener('resize', function (event) {
    window.updateGameHeaderHeight();
    window.updateActionHeaderHeight();
}, true);

// This function is usefull to be called upon from vuejs or other parts of teh system to re calculate the heights
// when an element has changed in the game header.
window.delayedUpdateAllResponsiveFunctions = function () {
    setTimeout(function () {
        window.updateGameHeaderHeight();
        window.updateActionHeaderHeight();
    }, 2500)
}