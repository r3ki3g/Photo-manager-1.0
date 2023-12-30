<!DOCTYPE html>
<html>

<body>

    <form method="post" action="">
        <input name="name" />
        <input type="submit" value="Submit" />


    </form>
    <div>
        <?php



        $data = $_POST["namerrrr"];
        if ($data) {
            echo "received data : {$data}";
        }

        ?>

    </div>
</body>

</html>