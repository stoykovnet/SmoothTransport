var user = null;

window.onload = function () {
    setUpLoginForm();
};

function setUpLoginForm() {
    document.getElementById('login-link').addEventListener('click', function () {
        loginLink_clicked();
    });
}

function loginLink_clicked() {
    var form = document.getElementById('login-form');

    if (form.username.value.trim().length && form.password.value.trim().length) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'http://localhost/smoothTransport/api/v1/user/?username='
                + form.username.value.trim()
                + '&password=' + form.password.value.trim(), true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                user = JSON.parse(xhr.responseText);

                if (user !== null) {
                    loadDashboard();
                } else {
                    document.getElementById('auth-fail-message').style.display = 'block';
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
        document.body.innerHTML = dash;

        document.getElementById('hello-name').innerHTML = user.first_name;
    });
}

/**
 * Get an html file from server.
 * @param {string} fileName
 * @param {function} callback
 */
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

function getSMSCount() {
    xhrGetNewSMSCount(function (count) {
        var counter = document.getElementById('sms-counter');
        if (count > 0) {
            counter.innerHTML = count;
            counter.style.display = 'block';
        } else {
            counter.style.display = 'none';
        }

        setTimeout(getSMSCount, 30000);
    });
}

function xhrGetNewSMSCount(callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'http://localhost/smoothTransport/api/v1/sms/?seen=0', true);
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