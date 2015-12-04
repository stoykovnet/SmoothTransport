window.onload = function () {
    if (!UILogin_callback.tryLogin(Cookie.read('logistician'))) {
        UILogin.setUp();
    }
};

/**
 * Static Class
 */
var Ajax = new function () {

    this.parseJSON = function (string) {
        try {
            var object = JSON.parse(string);
            return object;
        } catch (exception) {
            alert(exception + ':' + string);
            return null;
        }
    };

    this.getData = function (url, authorization, callback) {
        var xhr = new XMLHttpRequest();

        xhr.open('GET', url, true);
        if (authorization) {
            xhr.setRequestHeader('Authorization', authorization);
        }

        xhr.onload = function () {
            if (xhr.status === 200) {
                callback(xhr.responseText);
            } else {
                alert('Fail! ' + xhr.status + ':' + xhr.statusText);
                callback(null);
            }
        };

        xhr.send();
    };

    this.getFile = function (fileName, callback) {
        this.getData('view/' + fileName, false, callback);
    };
};
// Ajax Class ends.

/**
 * Static Class
 */
var Cookie = new function () {

    this.save = function (key, value) {
        var valueEncoded = escape(JSON.stringify(value));
        document.cookie = key + '=' + valueEncoded + ';';
    };

    this.read = function (key) {
        var cookies = document.cookie;
        if (cookies.indexOf(';')) {
            var cookiesArray = cookies.split(';');
            return Ajax.parseJSON(_find(key, cookiesArray));
        }

        return null;
    };

    function _find(key, cookies) {
        for (var i = 0; i < cookies.length; i++) {
            if (key === cookies[i].split('=')[0]) {
                return unescape(cookies[i].split('=')[1]);
            }
        }

        return null;
    }

    this.delete = function (key) {
        document.cookie = key + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
    };
};
// Cookie class ends.

/**
 * Static Class
 */
var UI = new function () {

    this.showElement = function (id, displayType) {
        var element = document.getElementById(id);
        element.style.display = displayType;
    };

    this.hideElement = function (id) {
        var element = document.getElementById(id);
        element.style.display = 'none';
    };

    this.enqueStyle = function (name) {
        if (!document.getElementById(name))
        {
            var link = document.createElement('link');
            link.id = name;
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = 'view/css/' + name + '.css';

            document.getElementsByTagName('head')[0].appendChild(link);
        }
    };

    this.validateInputValue = function (value) {
        if (value.trim().length > 2) {
            return true;
        }
        return false;
    };

    this.setUpLink = function (id, invoke) {
        var link = document.getElementById(id);
        link.addEventListener('click', invoke);
    };

    this.changePageTitle = function (title) {
        document.title = title;
    };

    this.capitalize = function (word) {
        word = word.toLowerCase();
        var capitalized = word.charAt(0).toUpperCase();
        var otherChars = word.slice(1);
        return capitalized + otherChars;
    };
};
// UI class ends.

/**
 * Static Class
 */
var UILogin = new function () {

    this.setUp = function () {
        UI.showElement('login', 'block');
        _setInputsBehavior();
        UI.setUpLink('login-link', UILogin_callback.loginLink_clicked);
    };

    function _setInputsBehavior() {
        var credentials = document.getElementsByClassName('credential');
        credentials[0].addEventListener('keyup', function (event) {
            (event.keyCode === 13) ? credentials[1].focus() : null;
        });
        credentials[1].addEventListener('keyup', function (event) {
            (event.keyCode === 13) ? UILogin_callback.loginLink_clicked() : null;
        });
    }
};
// UILogin class ends.

/**
 * Static Class
 */
var UILogin_callback = new function () {

    this.loggedLogistician = null;

    this.loginLink_clicked = function () {
        UI.hideElement('fail-message');

        var login = document.getElementById('login-form');
        var username = login.username.value, password = login.password.value;

        if (UI.validateInputValue(username) && UI.validateInputValue(password)) {
            _authenticateUser(username, password, function (logistician) {
                if (!_tryLogin(logistician)) {
                    UI.showElement('fail-message', 'block');
                }
            });
        } else {
            UI.showElement('fail-message', 'block');
        }
    };

    function _authenticateUser(username, password, callback) {
        var credentials = username + ':' + Sha256.hash(password);
        Ajax.getData('api/v1/logistician/', credentials, function (logistician) {
            callback(Ajax.parseJSON(logistician));
        });
    }

    function _tryLogin(logistician) {
        if (logistician) {
            _saveLogistician(logistician);
            UIDashboard.setUp();
            return true;
        }
        return false;
    }

    this.tryLogin = function (logistician) {
        return _tryLogin(logistician);
    };

    function _saveLogistician(logistician) {
        if (!Cookie.read('logistician')) {
            Cookie.save('logistician', logistician);
        }
        UILogin_callback.loggedLogistician = logistician;
    }
};
// UILogin_callback class ends.

/**
 * Static Class
 */
var UIDashboard = new function () {

    this.setUp = function () {
        _load();
    };

    function _load() {
        Ajax.getFile('dashboard.html', function (dashboard) {
            UI.enqueStyle('dashboard');
            document.body.innerHTML = dashboard;

            _updatePageTitle();
            _setUpGreeting();
            _setUpLinks();

            UIInbox.setUp();
        });
    }

    function _updatePageTitle() {
        var username = UILogin_callback.loggedLogistician.username;
        UI.changePageTitle(UI.capitalize(username) + ' :: Dashboard');
    }

    function _setUpGreeting() {
        var greeting = document.getElementById('greet-name');
        greeting.innerHTML = UILogin_callback.loggedLogistician.first_name;
    }

    function _setUpLinks() {
        UI.setUpLink('logout-link', UIDashboard_callback.logoutLink_clicked);
        UI.setUpLink('inbox-link', UIDashboard_callback.inboxLink_clicked);
    }
};
// UIDashboard class ends.

/**
 * Static Class
 */
var UIDashboard_callback = new function () {

    this.inboxLink_clicked = function () {
        UIInbox.setUp();
    };

    this.logoutLink_clicked = function () {
        Cookie.delete('logistician');
        location.reload();
    };
};
// UIDashboard_callback class ends.

/**
 * Static Class
 */
var UIInbox = new function () {

    this.setUp = function () {
        UIInbox_callback.autoCountUnseenMessages();
        _loadMessages();
    };

    this.updateUnseenCounter = function (number) {
        var counter = document.getElementById('unseen-counter');
        UI.hideElement(counter.id);

        if (number) {
            counter.innerHTML = ' (' + number + ')';
            UI.showElement(counter.id, 'inline-block');
        }
    };

    function _loadMessages() {
        alert('No messages');
        return null;
    }
};
//UIInbox class ends.

/**
 * Static Class
 */
var UIInbox_callback = new function () {

    this.autoCountUnseenMessages = function () {
        _countUnseenMessages(function (total) {
            UIInbox.updateUnseenCounter(total);
        });
        if (UILogin_callback.loggedLogistician) {
            setTimeout(this.autoCountUnseenMessages, 120 * 1000);
        }
    };

    function _countUnseenMessages(callback) {
        var id = UILogin_callback.loggedLogistician.id;
        var url = 'api/v1/logistician/' + id + '/sms/unseen/count/';
        Ajax.getData(url, false, callback);
    }
};