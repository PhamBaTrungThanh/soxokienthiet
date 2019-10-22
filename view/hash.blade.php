<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Hashing day {{$day}}</title>
  <meta name="author" content="Phạm Bá Trung Thành">

</head>

<body>
    <pre>
        -------------------------------------------------------------------------------------
        |                                                                                   |
        |                      CHƯƠNG TRÌNH DỰ ĐOÁN KẾT QUẢ XỔ SỐ                           |
        |                                                                                   |
        -------------------------------------------------------------------------------------
        | Tiến trình: HASHING                                                               |
        -------------------------------------------------------------------------------------
        | Ngày {{$day}}                                                                   |
        -------------------------------------------------------------------------------------
        | Kết thúc tiến trình, tự động chuyển sang ngày tiếp theo trong 1 giây nữa          | 
        -------------------------------------------------------------------------------------

    </pre>
@if($nextDay)
    <script>
        setTimeout(function () {
            location.reload()
        }, 1000);
        
    </script>
@endif
</body>
</html>