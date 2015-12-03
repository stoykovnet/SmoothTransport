var logistician = null;

window.onload = function () {
    logistician = readCookie('logistician');
    if (logistician) {
        loadDashboard();
    } else {
        setUpLoginForm();
    }
};

function enque_style(name) {
    if (!document.getElementById(name))
    {
        var head = document.getElementsByTagName('head')[0];
        var link = document.createElement('link');
        link.id = name;
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = 'view/css/' + name + '.css';
        head.appendChild(link);
    }
}

function xhrGetTemplatePart(fileName, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'view/' + fileName, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            if (callback) {
                callback(xhr.responseText);
            }
        } else {
            callback(0);
        }
    };
    xhr.send();
}

function writeCookie(key, value) {
    var valueEncoded = escape(JSON.stringify(value));
    document.cookie = key + '=' + valueEncoded + ';';
}

function readCookie(key) {
    var allValues = document.cookie;
    var value = null;

    if (allValues.toString().length > 0) {
        allValues = allValues.split(';');

        var i = 0, isFound = false;
        while (i < allValues.length && !isFound) {
            if (key === allValues[i].split('=')[0]) {
                value = unescape((allValues[i].split('=')[1]));
                try {
                    value = JSON.parse(value);
                } catch (exception) {
                    alert(value);
                }
                isFound = true;
            }
        }
    }
    return value;
}

function deleteCookie(key) {
    document.cookie = key +
            '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
}

function setUpLoginForm() {
    document.getElementById('username').addEventListener('keyup', function (event) {
        if (event.keyCode === 13) {
            document.getElementById('password').focus();
        }
    });

    document.getElementById('password').addEventListener('keyup', function (event) {
        if (event.keyCode === 13) {
            loginLink_clicked();
        }
    });

    document.getElementsByClassName('contents')[0].style.display = 'block';
    document.getElementById('login-link').addEventListener('click', function () {
        loginLink_clicked();
    });
}

function loginLink_clicked() {
    var form = document.getElementById('login-form');

    if (form.username.value.trim().length && form.password.value.trim().length) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'api/v1/logistician/', true);
        xhr.setRequestHeader('Authorization', form.username.value.trim() + ':' +
                Sha256.hash(form.password.value.trim()));

        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    logistician = JSON.parse(xhr.responseText);

                    if (logistician !== null) {
                        writeCookie('logistician', logistician);
                        loadDashboard();
                    } else {
                        document.getElementById('auth-fail-message').style.display = 'block';
                    }
                } catch (e) {
                    alert(xhr.responseText);
                }
            } else {
                alert('fail');
            }
        };
        xhr.send();
    }
}

function loadDashboard() {
    xhrGetTemplatePart('dashboard.html', function (dash) {
        enque_style('dashboard');
        document.body.innerHTML = dash;

        document.getElementById('hello-name').innerHTML = logistician.first_name;
        document.title = logistician.username.charAt(0).toUpperCase()
                + logistician.username.slice(1) + ' : Dashboard';

        showUnseenSMSCount();
        loadUnseenSMS();

        document.getElementById('new-link').addEventListener('click', function () {
            loadUnseenSMS();
        });

        document.getElementById('logout-link').addEventListener('click', function () {
            deleteCookie('logistician');
            location.reload();
        });
    });
}

function showUnseenSMSCount() {
    xhrGetUnseenSMSCount(function (count) {
        var counter = document.getElementById('unseen-counter');
        if (count > 0) {
            counter.innerHTML = ' (' + count + ')';
            counter.style.display = 'initial';
        } else {
            counter.style.display = 'none';
        }

        setTimeout(showUnseenSMSCount, 30000);
    });
}

function xhrGetUnseenSMSCount(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'http://localhost/smoothTransport/api/v1/logistician/' +
            logistician.id + '/sms/unseen/count/', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            if (callback) {
                callback(xhr.responseText);
                //alert(xhr.responseText);
            }
        } else {
            callback(0);
            //alert('Ajax error #2 occurred.');
        }
    };
    xhr.send();
}

function xhrGetUnseenSMS(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'http://localhost/smoothTransport/api/v1/logistician/' +
            logistician.id + '/sms/unseen/', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            if (callback) {
                callback(xhr.responseText);
                alert(xhr.responseText);
            }
        } else {
            callback(0);
            //alert('Ajax error #2 occurred.');
        }
    };
    xhr.send();
}

function loadUnseenSMS() {
    xhrGetUnseenSMS(function (messages) {
        if (messages) {
            var contents = document.getElementById('dashboard-contents');
            contents.style.display = 'block';
            try {
                messages = JSON.parse(messages);
                contents.innerHTML = '';
                for (var i = 0; i < messages.length; i++) {
                    xhrGetTruckData(i, messages[i].sender_id.id, function (x) {
                    });
                    contents.appendChild(createSMSElement(i, messages[i]));
                }
            } catch (e) {
                alert(messages);
            }
        }
    });
}

function xhrGetTruckData(i, truckDriverId, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'http://localhost/smoothTransport/api/v1/truck_driver/'
            + truckDriverId
            + '/truck', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            if (callback) {
                callback(xhr.responseText);
                //alert(xhr.responseText);
            }
        } else {
            callback(0);
            alert(i + ': ' + xhr.responseText);
        }
    };
    xhr.send();
}

function loadTruckData() {

}

function createSMSElement(id, data) {
    var sms = document.createElement('div');
    sms.setAttribute('id', 'sms-' + id);
    sms.setAttribute('class', 'sms');

    var messageColumn = document.createElement('div');
    messageColumn.setAttribute('class', 'sms-column');
    messageColumn.innerHTML =
            '<ul><li>' + id + '</li>'
            + '<li class="sms-meta-data">'
            + 'Stefan Stoykov'
            + '<a href="tel:' + id + '">'
            + '(' + id + ') ' + id + ' ' + id
            + '<span></span></a></li>'
            + '<li class="sms-meta-data">'
            + 'Received On: ' + '2015-12-02 11:35'
            + ' </li></ul>';

    var truckData = document.createElement('div');
    truckData.setAttribute('class', 'sms-column');
    truckData.innerHTML =
            '<ul><li>'
            + 'Drives: ' + 'Scania' + '"PC 00 000"'
            + '</li><li>'
            + 'Was last at: ' + 'Copenhagen'
            + '</li><li>'
            + 'Heading to: ' + 'Aalborg'
            + '</li></ul>';

    var clear = document.createElement('div');
    clear.setAttribute('class', 'clear');

    sms.appendChild(messageColumn);
    sms.appendChild(truckData);
    sms.appendChild(createSMSControlLinks(id));
    sms.appendChild(clear);
    return sms;
}

function createSMSControlLinks(id) {
    var controls = document.createElement('div');
    controls.setAttribute('class', 'sms-column last');

    var carriesLink = document.createElement('a');
    carriesLink.setAttribute('title', 'See what is carrying.');
    carriesLink.setAttribute('class', 'sms-control-link');
    carriesLink.setAttribute('id', 'carries-link-' + id);
    carriesLink.setAttribute('href', 'javascript:void(0);');
    carriesLink.innerHTML =
            '<div><img class="img-normal" src="view/img/carries_black_1.png" alt="Carries">'
            + '<img class="img-hover" src="view/img/carries_white_1.png" alt="Carries">'
            + '</div>';
    carriesLink.addEventListener('click', function () {
        alert(this.id);
    });

    var resolveLink = document.createElement('a');
    resolveLink.setAttribute('title', 'Resolve the issue');
    resolveLink.setAttribute('class', 'sms-control-link');
    resolveLink.setAttribute('id', 'resolve-link-' + id);
    resolveLink.setAttribute('href', 'javascript:void(0);');
    resolveLink.innerHTML =
            '<div><img class="img-normal" src="view/img/resolve_black_1.png" alt="Resolve">'
            + '<img class="img-hover" src="view/img/resolve_white_1.png" alt="Resolve">'
            + '</div>';
    resolveLink.addEventListener('click', function () {
        alert(this.id);
    });

    controls.appendChild(carriesLink);
    controls.appendChild(resolveLink);

    return controls;
}