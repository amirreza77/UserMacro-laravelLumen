<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body dir="rtl">
    <h1>درود بر شما ، {{ $name }}</h1>
    <p>این ایمیل جها فعال سازی حساب کاربری شما ارسال شده است</p>
    <p>جهت فعال سازی حساب کاربری خود بر روی لینک زیر کلیک بفرمایید</p>
    
        <a href="{{$url}}" style="background-color:#0984e3;color:#fff">
 فعال سازی
    </a>
</body>

</html>

