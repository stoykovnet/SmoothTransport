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
                alert('GET Fail! ' + xhr.status + ':' + xhr.statusText);
                callback(null);
            }
        };

        xhr.send();
    };

    this.postData = function (data, url, callback) {
        var xhr = new XMLHttpRequest();

        xhr.open('POST', url, true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                callback(xhr.responseText);
            } else {
                alert('POST Fail! ' + xhr.status + ':' + xhr.statusText);
                callback(null);
            }
        };

        xhr.send(JSON.stringify(data));
    };

    this.putData = function (data, url, callback) {
        var xhr = new XMLHttpRequest();

        xhr.open('PUT', url, true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                callback(xhr.responseText);
            } else {
                alert('PUT Fail! ' + xhr.status + ':' + xhr.statusText);
                callback(null);
            }
        };

        xhr.send(JSON.stringify(data));
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

    this.createElement = function (type, id, classType) {
        var element = document.createElement(type);

        (id) ? element.setAttribute('id', id) : null;
        (classType) ? element.setAttribute('class', classType) : null;

        return element;
    };

    this.createLink = function (text, href, id) {
        var link = this.createElement('a', id, null);
        link.setAttribute('href', href);
        link.innerHTML = text;
        return link;
    };

    this.createImage = function (src, classType) {
        var img = this.createElement('img', null, classType);
        img.setAttribute('src', src);
        return img;
    };

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

    this.getNumberInId = function (id) {
        var i = id.lastIndexOf('-');
        return id.substring(++i);
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

        if (!(UI.validateInputValue(username) && UI.validateInputValue(password))) {
            UI.showElement('fail-message', 'block');
            return;
        }

        _authenticateUser(username, password, function (logistician) {
            if (!_tryLogin(logistician)) {
                UI.showElement('fail-message', 'block');
            }
        });
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

    var wrap = null;

    this.getWrap = function () {
        return wrap;
    };

    this.setUp = function () {
        _load();
    };

    function _load() {
        Ajax.getFile('dashboard.html', function (dashboard) {
            UI.enqueStyle('dashboard');
            document.body.innerHTML = dashboard;
            wrap = document.getElementById('dashboard-contents');

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

    var inboxLinkIsActive = true;

    this.inboxLink_clicked = function () {
        if (inboxLinkIsActive) {
            UIInbox.setUp();
            UIResolve.remove();
            _lockLink('inbox');
        }
    };

    function _lockLink(linkName) {
        if (linkName === 'inbox') {
            inboxLinkIsActive = false;
            setTimeout(function () {
                inboxLinkIsActive = true;
            }, 2000);
        }
    }

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

    var messages = [];
    var wrap = null;

    this.getWrap = function () {
        return wrap;
    };

    this.setUp = function () {
        UIInbox_callback.autoCountUnseenMessages();
        _load();
    };

    this.updateUnseenCounter = function (number) {
        var counter = document.getElementById('unseen-counter');
        UI.hideElement(counter.id);

        if (number) {
            counter.innerHTML = ' (' + number + ')';
            UI.showElement(counter.id, 'inline-block');
        }
    };

    function _load() {
        UIInbox_callback.getAllMessages(function (receivedMessages) {
            messages = receivedMessages;
            _reset();
            _listMessages();
        });
    }

    function _listMessages() {
        for (var i = messages.length; i > 0; i--) {
            wrap.appendChild(UIMessage.wrapUp(messages[i - 1]));
            Messages.list.push(messages[i - 1]);
        }
    }

    function _reset() {
        if (wrap !== null) {
            wrap.parentNode.removeChild(wrap);
            UIMessage.resetCount();
        }
        DeliveryOrders.list = [];
        Drivers.list = [];
        Trucks.list = [];
        wrap = UI.createElement('div', 'inbox', 'inbox');
        UIDashboard.getWrap().appendChild(wrap);
    }

    this.hide = function () {
        wrap.style.display = 'none';
    };

    this.show = function () {
        wrap.style.display = 'initial';
    };

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
            setTimeout(UIInbox_callback.autoCountUnseenMessages, 180 * 1000);
        }
    };

    function _countUnseenMessages(callback) {
        var id = UILogin_callback.loggedLogistician.id;
        var url = 'api/v1/logistician/' + id + '/sms/unseen/count/';
        Ajax.getData(url, false, callback);
    }

    this.getAllMessages = function (callback) {
        var id = UILogin_callback.loggedLogistician.id;
        var url = 'api/v1/logistician/' + id + '/sms/';
        Ajax.getData(url, false, function (messages) {
            callback(Ajax.parseJSON(messages));
        });
    };
};
//UIInbox_callback ends.

/**
 * Static Class
 */
var UIMessage = new function () {

    var messageCount = 0;

    this.wrapUp = function (message) {
        var wrap = _createWrap(message.is_seen);
        wrap.appendChild(_addMessageData(message));
        wrap.appendChild(_addTruckData(message.sender_id.id, messageCount - 1));
        wrap.appendChild(_addControlLinks(messageCount - 1));
        wrap.appendChild(UI.createElement('div', null, 'clear'));
        return wrap;
    };

    function _addMessageData(message) {
        var column = _createColumn(null);
        var list = UI.createElement('ul', null, null);
        list.appendChild(_addText(message.message));
        list.appendChild(_addContactData(message.sender_id));
        list.appendChild(_addReceivedDate(message.saved_on_ts));
        column.appendChild(list);
        return column;
    }

    function _addText(message) {
        var text = UI.createElement('li', null, null);
        text.innerHTML = message;
        return text;
    }

    function _addContactData(sender) {
        Drivers.list.push(sender);
        var data = UI.createElement('li', null, 'sms-meta-data');
        data.innerHTML = sender.first_name + ' ' + sender.last_name + ' ';

        var telephone = _formatTelephone(sender.telephone);
        data.appendChild(UI.createLink(telephone, 'tel:' + sender.telephone, null));

        return data;
    }

    function _addReceivedDate(date) {
        var data = UI.createElement('li', null, 'sms-meta-data');
        data.innerHTML = 'Received On: ' + date.substring(0, 16);
        return data;
    }

    function _formatTelephone(telephone) {
        return '(' + telephone.substring(0, 3) + ') '
                + telephone.substring(3, 7) + ' '
                + telephone.substring(7);
    }

    function _createWrap(isSeen) {
        if (parseInt(isSeen)) {
            var wrap = UI.createElement('div', 'sms-' + messageCount++, 'sms');
            wrap.appendChild(UI.createImage('view/img/seen.png', 'is-seen-img'));
            return wrap;
        }

        var wrap = UI.createElement('div', 'sms-' + messageCount++, 'sms unseen');
        wrap.appendChild(UI.createImage('view/img/unseen.png', 'is-seen-img'));
        return wrap;
    }

    function _createColumn(id) {
        return UI.createElement('div', id, 'sms-column');
    }

    function _addTruckData(driverId, currentCount) {
        UIMessage_callback.getDriversTruck(driverId, function (truck) {
            Trucks.list.push(truck);

            var li = UI.createElement('li', null, null);
            li.innerHTML = truck.brand + ' "' + truck.number_plate + '"';

            var ul = UI.createElement('ul', 'delivery-data-' + currentCount, null);
            ul.appendChild(li);
            document.getElementById('delivery-column-' + currentCount).appendChild(ul);

            _addDeliveryOrderData(truck.id, currentCount);
        });
        var column = _createColumn('delivery-column-' + currentCount);
        return column;
    }

    function _addDeliveryOrderData(truckId, currentCount) {
        UIMessage_callback.getDeliveryOrder(truckId, function (order) {
            var wasAt = UI.createElement('li', null, null);
            wasAt.innerHTML = 'Was last at: ' + order.manufacturer_id.location;
            var headingTo = UI.createElement('li', null, null);
            headingTo.innerHTML = 'Heading to: ' + order.shop_id.location;

            var ul = document.getElementById('delivery-data-' + currentCount);
            ul.appendChild(wasAt);
            ul.appendChild(headingTo);
        });
    }

    function _addControlLinks(currentCount) {
        var controls = _createColumn(null);
        controls.setAttribute('class', 'last');
        controls.appendChild(_createCarriesLink(currentCount));
        controls.appendChild(_createResolveLink(currentCount));
        return controls;
    }

    function _createCarriesLink(currentCount) {
        var carriesLink = UI.createLink(_getControlLinkImages('carries'),
                'javascript:void(0);', 'carries-link-' + currentCount);
        carriesLink.setAttribute('title', 'See what it is carrying.');
        carriesLink.setAttribute('class', 'sms-control-link');
        carriesLink.addEventListener('click', UIMessage_callback.carriesLink_clicked);
        return carriesLink;
    }

    function _createResolveLink(currentCount) {
        var resolveLink = UI.createLink(_getControlLinkImages('resolve'),
                'javascript:void(0);', 'resolve-link-' + currentCount);
        resolveLink.setAttribute('title', 'Resolve issue');
        resolveLink.setAttribute('class', 'sms-control-link');
        resolveLink.addEventListener('click', UIMessage_callback.resolveLink_clicked);
        return resolveLink;
    }

    function _getControlLinkImages(linkName) {
        return '<div><img class="img-normal" src="view/img/' + linkName + '_black_1.png" alt="Carries">'
                + '<img class="img-hover" src="view/img/' + linkName + '_white_1.png" alt="Carries">'
                + '</div>';
    }

    this.resetCount = function () {
        messageCount = 0;
    };
};
// UIMessage class ends.

/**
 * Static Class
 */
var UIMessage_callback = new function () {

    this.getDriversTruck = function (id, callback) {
        var url = 'api/v1/truck_driver/' + id + '/truck/';
        Ajax.getData(url, false, function (truck) {
            callback(Ajax.parseJSON(truck));
        });
    };

    this.getDeliveryOrder = function (truckId, callback) {
        var url = 'api/v1/truck/' + truckId + '/delivery_order/';
        Ajax.getData(url, false, function (order) {
            order = Ajax.parseJSON(order);
            DeliveryOrders.list.push(order);
            callback(order);
        });
    };

    this.carriesLink_clicked = function () {
        alert(JSON.stringify(DeliveryOrders.list[UI.getNumberInId(this.id)]));
    };

    this.resolveLink_clicked = function () {
        var issueNumber = UI.getNumberInId(this.id);
        UIInbox.hide();
        UIResolve.setUp(issueNumber);
        if (Messages.list[issueNumber].is_seen === '0') {
            _sendAffirmingMessageToDriver(Drivers.list[issueNumber]);
            _markMessageSeen(issueNumber);
        }
    };

    function _sendAffirmingMessageToDriver(driver) {
        var url = 'api/v1/truck_driver/' + driver.id + '/sms/';
        var data = {
            senderId: UILogin_callback.loggedLogistician.id,
            recipientId: driver.id,
            recipientTelephone: driver.telephone,
            message: UILogin_callback.loggedLogistician.first_name +
                    ' has seen your message and is working on the issue.'
        };

        Ajax.postData(data, url, function () {
        });
    }

    function _markMessageSeen(issueNumber) {
        var url = 'api/v1/sms/' + Messages.list[issueNumber].id;
        Messages.list[issueNumber].is_seen = true;
        Ajax.putData(Messages.list[issueNumber], url, function () {
            var sms = document.getElementById('sms-' + issueNumber);
            sms.setAttribute('class', 'sms');
        });
    }
};
// UIMessage_callback class ends.

/**
 * Static Class
 */
var DeliveryOrders = new function () {

    this.list = [];
};

/**
 * Static Class
 */
var Drivers = new function () {

    this.list = [];
};

/**
 * Static Class
 */
var Messages = new function () {

    this.list = [];
};

/**
 * Static Class
 */
var ServiceStations = new function () {

    this.list = [];
};

/**
 * Static Class
 */
var Trucks = new function () {

    this.list = [];
};

/**
 * Static Class
 */
var UIResolve = new function () {

    var wrap = null;

    this.getWrap = function () {
        return wrap;
    };

    this.setUp = function (resolveId) {
        _load(resolveId);
        UIInbox.hide();
    };

    function _load(resolveId) {
        Ajax.getFile('resolve-wrap.html', function (resolveWrap) {
            UI.enqueStyle('resolve-wrap');
            wrap = UI.createElement('div', 'resolve-wrap', 'resolve-wrap');
            wrap.innerHTML = resolveWrap;
            wrap.appendChild(_createCloseLink());
            UIDashboard.getWrap().appendChild(wrap);
            _addData(resolveId);
        });
    }

    function _addData(resolveId) {
        _addDriverData(resolveId);
        _addTruckData(resolveId);
        _addDeliveryOrderData(resolveId);
        _listServiceStations(resolveId);
    }

    function _addDriverData(resolveId) {
        driver = Drivers.list[resolveId];
        var names = document.getElementById('truck-driver-names');
        names.innerHTML = driver.first_name + ' ' + driver.last_name;

        var telephone = document.getElementById('truck-driver-telephone');
        telephone.setAttribute('href', 'tel:' + driver.telephone);
        telephone.innerHTML = _formatTelephone(driver.telephone);
    }

    function _formatTelephone(telephone) {
        return '(' + telephone.substring(0, 3) + ') '
                + telephone.substring(3, 7) + ' '
                + telephone.substring(7);
    }

    function _addTruckData(resolveId) {
        UIResolve_callback.getDriversTruck(Drivers.list[resolveId].id, function (truck) {
            var drives = document.getElementById('truck-driver-drives');
            drives.innerHTML = truck.brand + ' "' + truck.number_plate + '"';
        });
    }

    function _addDeliveryOrderData(resolveId) {
        var order = DeliveryOrders.list[resolveId];
        var wasAt = document.getElementById('was-at');
        wasAt.innerHTML = order.manufacturer_id.location;

        var headTo = document.getElementById('heading-to');
        headTo.innerHTML = order.shop_id.location;
    }

    function _createCloseLink() {
        var closeLink = UI.createLink('Close', 'javascript:void(0);', 'close-link');
        closeLink.setAttribute('class', 'control-link');
        closeLink.addEventListener('click', UIResolve_callback.closeLink_clicked);
        return closeLink;
    }

    function _listServiceStations(resolveId) {
        UIResolve_callback.getServiceStations(function (stations) {
            var list = document.getElementById('service-station-list');
            for (var i = 0; i < stations.length; i++) {
                list.appendChild(_addServiceStation(i, stations[i], resolveId));
                ServiceStations.list.push(stations[i]);
            }
        });
    }

    function _addServiceStation(i, station, resolveId) {
        var tile = UI.createElement('div', 'station-' + i, 'tile');
        tile.appendChild(_addServiceStationNameAndLocation(station));
        tile.appendChild(_addServiceStationGPSCoordinates(station));
        tile.appendChild(UI.createElement('div', null, 'clear'));
        tile.addEventListener('click', function () {
            UIResolve_callback.serviceStation_clicked(this.id, resolveId);
        });
        return tile;
    }

    function _addServiceStationNameAndLocation(station) {
        var col = UI.createElement('div', null, 'service-station-column');
        var ul = UI.createElement('ul', null, null);

        ul.appendChild(_createDataRow('Name and Location'));
        ul.appendChild(_createDataRow(station.name + ', ' + station.location));

        col.appendChild(ul);
        return col;
    }

    function _addServiceStationGPSCoordinates(station) {
        var col = UI.createElement('div', null, 'service-station-column');
        var ul = UI.createElement('ul', null, null);

        ul.appendChild(_createDataRow('GPS Coordinates'));
        ul.appendChild(_createDataRow(station.longitude + ' ' + station.latitude));

        col.appendChild(ul);
        return col;
    }

    function _createDataRow(data) {
        var row = UI.createElement('li', null, null);
        row.innerHTML = data;
        return row;
    }

    this.remove = function () {
        if (wrap) {
            wrap.parentNode.removeChild(wrap);
        }

        wrap = null;
    };

};
// UIResolve class ends.

/**
 * Static Class
 */
var UIResolve_callback = new function () {

    this.closeLink_clicked = function () {
        UIResolve.remove();
        UIInbox.show();
        ServiceStations.list = [];
    };

    this.getDriversTruck = function (id, callback) {
        var url = 'api/v1/truck_driver/' + id + '/truck/';
        Ajax.getData(url, null, function (truck) {
            callback(Ajax.parseJSON(truck));
        });
    };

    this.getServiceStations = function (callback) {
        var url = 'api/v1/point_of_interest/services/';
        Ajax.getData(url, null, function (services) {
            callback(Ajax.parseJSON(services));
        });
    };

    this.serviceStation_clicked = function (stationId, resolveId) {
        UIResolve.getWrap().style.display = 'none';
        UIConfirmMessage.setUp(stationId, resolveId);
    };
};
// UIResolve_callback class ends.

/**
 * Static Class
 */
var UIConfirmMessage = new function () {

    var wrap = null;

    this.getWrap = function () {
        return wrap;
    };

    this.setUp = function (serviceId, resolveId) {
        _load(serviceId, resolveId);
    };

    function _load(serviceId, resolveId) {
        Ajax.getFile('confirm-message.html', function (html) {
            UI.enqueStyle('confirm-message');
            wrap = UI.createElement('div', 'confirm-message-wrap', 'confirm-message-wrap');
            wrap.innerHTML = html;
            UIDashboard.getWrap().appendChild(wrap);
            _addGeneratedMessage(serviceId, resolveId);
            _setUpControlLinks(serviceId, resolveId);
        });
    }

    function _addGeneratedMessage(serviceId, resolveId) {
        _addTruckDriverData(Drivers.list[UI.getNumberInId(resolveId)]);
        _addStationData(ServiceStations.list[UI.getNumberInId(serviceId)]);
        _addTruckData(Trucks.list[UI.getNumberInId(resolveId)]);
    }

    function _addTruckDriverData(driver) {
        var names = document.getElementById('message-recipient');
        names.innerHTML = driver.first_name + ' ' + driver.last_name;
    }

    function _addStationData(station) {
        var stationSpan = document.getElementById('service-station-data');
        stationSpan.innerHTML = station.name + ', located at '
                + station.location + '. GPS: ' + station.longitude + ' '
                + station.latitude;
    }

    function _addTruckData(truck) {
        var brand = document.getElementById('truck-brand');
        brand.innerHTML = truck.brand;
        var model = document.getElementById('truck-model');
        model.innerHTML = truck.model;
        var engine = document.getElementById('truck-engine');
        engine.innerHTML = truck.engine;
        var tires = document.getElementById('truck-tires');
        tires.innerHTML = truck.tires_serial;
    }

    function _setUpControlLinks(stationId, resolveId) {
        var noSendLink = document.getElementById('no-send-link');
        var yesSendLink = document.getElementById('yes-send-link');

        noSendLink.addEventListener('click', UIConfirmMessage_callback.noSendLink_clicked);
        yesSendLink.addEventListener('click', function () {
            UIConfirmMessage_callback.yesSendLink_clicked(stationId, resolveId);
        });
    }

    this.remove = function () {
        UIResolve.getWrap().style.display = 'initial';

        if (wrap) {
            wrap.parentNode.removeChild(wrap);
        }

        wrap = null;
    };
};
// UIConfirmMessage class ends.

/**
 * Static Class
 */
var UIConfirmMessage_callback = new function () {

    this.noSendLink_clicked = function () {
        UIConfirmMessage.remove();
    };

    this.yesSendLink_clicked = function (stationId, resolveId) {
        var msg = _generateMessage(stationId, resolveId);
        _sendMessageToDriver(Drivers.list[UI.getNumberInId(resolveId)], msg, resolveId);
        UIConfirmMessage.getWrap().style.display = 'none';
    };

    function _generateMessage(serviceId, resolveId) {
        var messageToSend = '';
        messageToSend += _addStationData(ServiceStations.list[UI.getNumberInId(serviceId)]);
        messageToSend += _addTruckData(Trucks.list[UI.getNumberInId(resolveId)]);
        return messageToSend;
    }

    function _addStationData(station) {
        return 'Go to ' + station.name + ', located at '
                + station.location + '. GPS: ' + station.longitude + ' '
                + station.latitude + '\n';
    }

    function _addTruckData(truck) {
        return 'Truck information - brand: ' + truck.brand + ', '
                + 'model: ' + truck.model + ', '
                + 'engine: ' + truck.engine + ', '
                + 'tires: ' + truck.tires_serial + ', ';
    }

    function _sendMessageToDriver(driver, messageToSend, resolveId) {
        var url = 'api/v1/truck_driver/' + driver.id + '/sms/';
        var data = {
            senderId: UILogin_callback.loggedLogistician.id,
            recipientId: driver.id,
            recipientTelephone: driver.telephone,
            message: messageToSend
        };

        Ajax.postData(data, url, function (isSent) {
            if (isSent) {
                _markMessageResolved(resolveId);
            } else {
                _showFailSent();
            }
        });
    }

    function _showFailSent() {
        UIConfirmMessage.getWrap().style.display = 'initial';
        document.getElementById('fail-sending').style.display = 'initial';
    }

    function _markMessageResolved(resolveId) {
        UIConfirmMessage.remove();
        UIResolve.remove();
        var url = 'api/v1/sms/' + Messages.list[resolveId].id;

        Messages.list[resolveId].is_resolved = true;

        Ajax.putData(Messages.list[resolveId], url, function () {
            var sms = document.getElementById('sms-' + resolveId);
            sms.parentNode.removeChild(sms);
            
            UIInbox.show();
        });
    }

};
// UIConfirmMessage_callback class ends.