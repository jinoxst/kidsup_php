<?php

class Constant {
    const CONTROLLER = 'Controller';
    const ACTION = 'Action';
    const LOG_DIR = '/var/www/html/web/data/logs/';
    const NETWORK_TIMEOUT = 15;
    const CRYPTO_KEY = '9986b4802ba336f6';
    const CRYPTO_IV = '12595b352fcca3e2';

    const SESSION_TIMEOUT = 1800;//30min -> developmemnt
    //const SESSION_TIMEOUT = 10800;//3hour -> production
    const SESSION_LOGOUT_TIMEOUT = 0;
    const COOKIE_TIMEOUT = 2592000;//1month
    const COOKIE_TIMEOUT_ONEYEAR = 31536000;//1year
    const COOKIE_TIMEOUT_FLAG = 'COOKIE_TIMEOUT_FLAG';
    const COOKIE_PATH = '/web/public';
    const SESSION_NAMESPACE_NAME = 'WEB_SESSION';

    const LOGIN_OK = 1;
    const LOGIN_NG = 0;
    const MIN_YEAR = 2015;
    const IMG_HOST_URL = 'http://52.68.76.51/';
    //const IMG_HOST_URL = 'http://www.kidsup.net';
    const NO_IMG_URL = 'http://www.kidsup.net/images/main/member/thm/356a192b7913b04c54574d18c28d46e6395428ab.jpg';
    const SEAR_STR = 'SESSION_EXPIRED_AJAX_REQUEST';

    const PAGESIZE = 10;
    const GROUPSIZE = 10;
    const ALBUMPAGESIZE = 20;

    const IMAGE_WORK_DIR = '/var/www/html/images/work/';
    const IMAGE_MAIN_DIR = '/var/www/html/images/main/';
    const IMAGE_ORG_DIR = 'org';
    const IMAGE_CHG_DIR = 'chg';
    const IMAGE_THM_DIR = 'thm';
    const THM_IMG_TAIL = '_thm';
    const CONTACT_DIR = 'contact';
    const NOTICE_DIR = 'notice';
    const MAMATALK_DIR = 'mamatalk';
    const DAILYMENU_DIR = 'dailymenu';
    const ORG_IMG_WIDTH = 960;
    const ORG_IMG_HEIGHT = 1280;//used nowhere
    const CHG_IMG_WIDTH = 400;
    const THM_IMG_WIDTH = 200;
    const PROFILE_IMG_WIDTH = 200;
    const NEWLINE = '__N__';

    const GOOGLE_API_ACCESS_KEY = 'AIzaSyBSJ4tY-uaHfPBnPTT5yuJlo9vVdCgXLdY';
}