<!-- #Use link link to setWebhook to telegram bot " Vattra PHP" 
#{api_telegram_web}+bot{api}+setWebhook?url={ngrok}+{file_path}
https://api.telegram.org/bot7711596255:AAE81RdAldNaF09rfuFWGsI1nNka_uFArgg/setWebhook?url=3a75-119-82-250-189.ngrok-free.app/t-b1/bot.php -->
<?php
$filePath= 'Erksanthan/Erksanthan.php';
$domain =  'https://ranavattra.com' ;
$token = '8254931317:AAHBFFJwJg6KmCtynrXkkUPqyxEdFkSNCsY';

// $domain =  'https://4dea-119-82-250-189.ngrok-free.app' ;


$setWebhook = 'https://api.telegram.org/bot'.$token.'/setWebhook?url='.$domain."/$filePath";

// file_get_contents($webhook, true);
header("Location: ".$setWebhook);
echo $webhook;
?>