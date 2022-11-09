function tabClick(tabId) {

    o('#searchButton').className = '';
    o('#accessButton').className = '';
    o('#restrictedButton').className = '';

    o('#' + tabId).className = 'currentTab';

    switch(tabId) {

        case 'searchButton':
            o('#searchTab').style.display = '';
            o('#accessTab').style.display = 'none';
            o('#restrictedTab').style.display = 'none';
            break;

        case 'accessButton':
            o('#searchTab').style.display = 'none';
            o('#accessTab').style.display = '';
            o('#restrictedTab').style.display = 'none';
            break;

        case 'restrictedButton':
            o('#searchTab').style.display = 'none';
            o('#accessTab').style.display = 'none';
            o('#restrictedTab').style.display = '';
            break;
    }
}
