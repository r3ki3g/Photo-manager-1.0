<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php




    ?>
    <form action="" method="post">
        <input type="name" name="name" placeholder="Enter your name" /><br /><br/>
        <input type="radio" name="sex" value="male" id="maleChoice" /><label for="maleChoice">Male</label>
        <input type="radio" name="sex" value="female" id="femaleChoice"/><label for="femaleChoice">Female</label>
        <br/><br/>Birth year
        <select name="birthyear"><?php
        for($i=2020;$i>=1940;$i--)
        {
            echo '<option value="'. $i . '">' . $i .'</option>';
        }
        ?>
        </select><br/><br/>
        <input type="checkbox" name="sl" id="slChoice"/><label for="slChoice">I'm a Sri Lankan</label><br/><br/>
        <input type="submit" value="Next" />
    </form>
</body>

</html>