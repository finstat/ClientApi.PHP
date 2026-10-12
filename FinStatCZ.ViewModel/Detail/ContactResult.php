<?php

namespace FinStatCZ\ViewModel\Detail;

class ContactResult
{
    // EliteCZ returns Email and Phone, UltimateCZ adds web pages and social networks
    const TYPE_EMAIL = 'Email';
    const TYPE_PHONE = 'Phone';
    const TYPE_WEB = 'Web';
    const TYPE_LINKEDIN = 'LinkedIn';
    const TYPE_FACEBOOK = 'Facebook';
    const TYPE_INSTAGRAM = 'Instagram';
    const TYPE_X = 'X';
    const TYPE_YOUTUBE = 'YouTube';
    const TYPE_TIKTOK = 'TikTok';

    public $Contact;
    public $Type;
    public $Sources = array();
}
