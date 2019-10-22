<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Caculating day {{$date}}</title>
  <meta name="author" content="Phạm Bá Trung Thành">

</head>

<body>
    <pre>
        -------------------------------------------------------------------------------------
        |                                                                                   |
        |                      CHƯƠNG TRÌNH DỰ ĐOÁN KẾT QUẢ XỔ SỐ                           |
        |                                                                                   |
        -------------------------------------------------------------------------------------
        | Ngày {{$date}}                                                                   |
        -------------------------------------------------------------------------------------
        | Tự động chuyển đến ngày {{$nextLottery->rolled_at->format("Y-m-d")}}                                                |
        -------------------------------------------------------------------------------------
    </pre>
    @if($nextLottery)
    <script>
        setTimeout(function () {
           window.location.href = "/compute?date={{$nextLottery->rolled_at->format("Y-m-d")}}"
        }, 1000);
        
    </script>
    @endif
</body>
</html>