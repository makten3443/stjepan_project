    <?php 
    //require_once("./object/userdbhandler.php");
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script>
        $(document).ready(() => {
            $(".form").on("submit", (e) => {
                e.preventDefault();
                var data = $(e.target).serialize();
                if ($(e.target).find('input[name="method"]:checked').val() === "get") {
                    $.get($(e.target).find('input[name="url"]').val(), data, (data) => {
                        $("#serverResponse").val(JSON.stringify(data));
                    });
                } else {
                    $.post($(e.target).find('input[name="url"]').val(), data, (data) => {
                        $("#serverResponse").val(JSON.stringify(data));
                    });
                }
            });
        });
    </script>
</head>
<body>
    
    <form method="POST" action="api.php">
    <input type="text" name="firstname" placeholder="Firstname" /><br />
    <input type="text" name="lastname" placeholder="Lastname" /><br />
    <input type="text" name="username" placeholder="username" /><br />
    <input type="password" name="password" placeholder="password" /><br />
    <input type="email" name="email" placeholder="email" /><br />
    <input type="submit" />
    </form>
    <div>
    
    </div>

    
</body>
</html>