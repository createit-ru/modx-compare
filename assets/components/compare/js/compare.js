Compare = {
    options: {
        //actionUrl: '/Extras/compare/assets/components/compare/action.php',

        actionUrl: '/assets/components/compare/action.php',
    },
    selectors: {
        mini: '.compare-mini',
        miniCount: '.compare-mini__count',
    },
    classes: {
        miniActive: 'compare-mini--active',
        compareButton: 'compare__button',
    },
    add: {
        options: {

        },
        initialize: async function (selector) {
            let elements = document.querySelectorAll(selector);
            let ids = [];
            elements.forEach(function (element) {
                if ('id' in element.dataset) {
                    ids.push({
                        id: element.dataset.id,
                        list: ('list' in element.dataset) ? element.dataset.list : 'default',
                    });
                }

                let btn = element.getElementsByClassName(Compare.classes.compareButton)[0];

                btn.addEventListener('click', async function (e) {
                    e.preventDefault();
                    let compareEl = e.target || e.srcElement;
                    // это небольшой хак для ссылок, внутри которых есть тег img или svg
                    if (!compareEl.hasAttribute('compare-root')) {
                        compareEl = compareEl.closest('[compare-root]');
                    }

                    let id = compareEl.dataset.id;
                    let list = ('list' in compareEl.dataset) ? compareEl.dataset.list : 'default';

                    let responseData = await Compare.fetch({
                        action: 'toggle',
                        id: id,
                        list: list
                    });

                    if (responseData.success) {
                        let compareInfo = responseData.data[id][list];
                        Compare.add.setState(compareEl, compareInfo.added, compareInfo.count);
                        await Compare.mini.load();
                    }
                    else {
                        console.error(responseData.message);
                        return;
                    }

                    return false;
                });
            });

            if (ids.length > 0) {
                let responseData = await Compare.fetch({
                    action: 'load',
                    ids: ids
                });

                if (responseData.success) {
                    elements.forEach(function (element) {
                        if ('id' in element.dataset) {
                            let id = element.dataset.id;
                            let list = ('list' in element.dataset) ? element.dataset.list : 'default';
                            const compareInfo = responseData.data[id][list];
                            Compare.add.setState(element, compareInfo.added, compareInfo.count);
                        }
                    });
                }
                else {
                    console.error(responseData.message);
                }
            }
        },
        setState: function (element, added, count) {
            element.classList.remove('compare--load');
            if (added) {
                element.classList.add('compare--added');
            }
            else {
                element.classList.remove('compare--added');
            }

            if (count > 0) {
                element.classList.add('compare--can');
            } else {
                element.classList.remove('compare--can');
            }
            let countElements = element.getElementsByClassName('compare__count');
            for (let i = 0; i < countElements.length; i++) {
                countElements[i].innerHTML = count;
            }

            let textElements = element.getElementsByClassName('compare__button-text');
            for (let i = 0; i < textElements.length; i++) {
                if (added) {
                    textElements[i].innerHTML = textElements[i].dataset['remove'];
                }
                else {
                    textElements[i].innerHTML = textElements[i].dataset['add'];
                }

            }
        }
    },
    mini: {
        initialize: function () {
            Compare.mini.load();
        },
        load: async function () {
            let responseData = await Compare.fetch({
                action: 'mini',
            });

            if (responseData.success) {
                Compare.mini.setState(responseData.data.count);
            }
            else {
                Compare.mini.setState(0);
                console.error(responseData.message);
            }
        },
        setState: function (count) {
            let all = document.querySelectorAll(Compare.selectors.mini);
            all.forEach(function (mini) {
                if (count > 0) {
                    mini.classList.add(Compare.classes.miniActive);
                } else {
                    mini.classList.remove(Compare.classes.miniActive);
                }
                let countEl = mini.querySelector(Compare.selectors.miniCount);
                if (countEl) {
                    countEl.innerHTML = count;
                }
            });
        }
    },
    page: {
        initialize: function (selector) {
            let page = document.querySelector(selector);
            if (!page) {
                return;
            }

            let checkboxDifferences = page.querySelector('input[name="differences"]');
            let grid = page.querySelector('.compare-grid');
            if (checkboxDifferences && grid) {
                checkboxDifferences.addEventListener('change', function () {
                    if (this.checked) {
                        grid.classList.add('compare-grid--only-differences');
                    } else {
                        grid.classList.remove('compare-grid--only-differences');
                    }
                });
                if (checkboxDifferences.checked) {
                    grid.classList.add('compare-grid--only-differences');
                }
            }

            let removeButtons = page.querySelectorAll('.js-compare-remove');
            removeButtons.forEach(function (button) {
                button.addEventListener('click', async function (el) {
                    let btn = el.target;
                    let id = btn.dataset.id;
                    let list = btn.dataset.list;
                    if (id && list) {
                        let responseData = await Compare.fetch({
                            action: 'remove',
                            id: id,
                            list: list
                        });

                        if (responseData.success) {
                            location.reload();
                        }
                        else {
                            console.error(responseData.message);
                        }
                    }
                    else {
                        console.error('Add the product id and list id to the data attributes for the button.');
                    }
                })
            });

            let cleanButtons = page.querySelectorAll('.js-compare-clean');
            cleanButtons.forEach(function (button) {
                button.addEventListener('click', async function (el) {
                    let btn = el.target;
                    let list = btn.dataset.list;
                    if (list) {
                        let responseData = await Compare.fetch({
                            action: 'clean',
                            list: list
                        });

                        if (responseData.success) {
                            location.reload();
                        }
                        else {
                            console.error(responseData.message);
                        }
                    }
                    else {
                        console.error('Add the list id to the data attributes for the button.');
                    }
                })
            });
        }
    },
    fetch: async function (data) {
        let fetchOptions = {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify(data)
        };

        let response = await fetch(Compare.options.actionUrl, fetchOptions);
        return await response.json();
    }
};

document.addEventListener("DOMContentLoaded", function () {
    Compare.mini.initialize();
    Compare.add.initialize(".compare--load");
    Compare.page.initialize(".compare-page");
});

