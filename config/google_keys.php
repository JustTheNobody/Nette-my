<?php //google_keys.php

//other site settings

// Google reCAPTCHA v3 keys
// For reducing spam contact form submissions

// Site key (public)
$reCAPTCHA_site_key = '6Lfh0KwaAAAAAACS3MWteDgw8iXbshwcIVWT4iUj';

// Secret key
$reCAPTCHA_secret_key = '6Lfh0KwaAAAAACJ3Nw6k55uBUbc4mnB9lI2KHad7';

// Min score returned from reCAPTCHA to allow form submission
$g_recaptcha_allowable_score = 0.5; //Number between 0 and 1. You choose this. Setting a number closer to 0 will let through more spam, closer to 1 and you may start to block valid submissions.