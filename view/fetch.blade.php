<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Fetching process for xskt - {{$lottery->date->diffInDays()}} to go</title>
  <meta name="author" content="Phạm Bá Trung Thành">

</head>

<body>
    <pre>
        ----------------------------------------------------------
        |           CHƯƠNG TRÌNH DỰ ĐOÁN KẾT QUẢ XỔ SỐ           |
        ----------------------------------------------------------
        | Dữ liệu ngày {{$lottery->date->format("d/m/Y")}}                                |
        ----------------------------------------------------------
    @if(!$is_new_year)
    @else
    | Nghỉ tết không có dữ liệu                              |
        ---------------------------------------------------------- 
    @endisset
    </pre>

    @isset($limitReached)
    @if($limitReached)
    <pre>

        | Dừng tải dữ liệu                                       |
        ----------------------------------------------------------
    </pre>

    @else
    <script>
        setTimeout(function () {
            location.reload()
        }, 1000);
       
    </script>
    @endif
    @endisset
</body>
</html>