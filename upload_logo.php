<?php

if(isset($_FILES["logo"])){

    move_uploaded_file(
        $_FILES["logo"]["tmp_name"],
        "logo.png"
    );

    echo json_encode([
        "success"=>true
    ]);

}
