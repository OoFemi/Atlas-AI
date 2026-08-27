<?php

session_start();

/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["admin"]) ||
    $_SESSION["admin"] !== true
) {

    header(
        "Location: admin.html"
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Check Upload
|--------------------------------------------------------------------------
*/

if (
    !isset($_FILES["document"]) ||
    $_FILES["document"]["error"] === UPLOAD_ERR_NO_FILE
) {

    header(
        "Location: documents.php?upload=failed"
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Department
|--------------------------------------------------------------------------
*/

$department =
trim(
    $_POST["department"] ?? ""
);

if ($department === "") {

    header(
        "Location: documents.php?upload=failed"
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Sanitize Department
|--------------------------------------------------------------------------
*/

$department =
preg_replace(
    "/[^a-zA-Z0-9_-]/",
    "",
    $department
);

/*
|--------------------------------------------------------------------------
| Upload Folder
|--------------------------------------------------------------------------
*/

$baseFolder =
"/home/femi/n8n-production/files/";

$departmentFolder =
$baseFolder .
$department .
"/";

/*
|--------------------------------------------------------------------------
| Create Folder If Missing
|--------------------------------------------------------------------------
*/

if (
    !is_dir($departmentFolder)
) {

    if (
        !mkdir(
            $departmentFolder,
            0775,
            true
        )
    ) {

        header(
            "Location: documents.php?upload=failed"
        );

        exit;

    }

}

/*
|--------------------------------------------------------------------------
| File Validation
|--------------------------------------------------------------------------
*/

$fileName =
basename(
    $_FILES["document"]["name"]
);

$fileName =
preg_replace(
    "/[^a-zA-Z0-9._-]/",
    "_",
    $fileName
);

$fileExtension =
strtolower(
    pathinfo(
        $fileName,
        PATHINFO_EXTENSION
    )
);

$allowedExtensions = [

    "pdf",
    "doc",
    "docx",
    "txt",
    "csv",
    "xls",
    "xlsx"

];

if (

    !in_array(
        $fileExtension,
        $allowedExtensions
    )

) {

    header(
        "Location: documents.php?upload=failed"
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| PHP Upload Error Check
|--------------------------------------------------------------------------
*/

if (
    $_FILES["document"]["error"] !==
    UPLOAD_ERR_OK
) {

    header(
        "Location: documents.php?upload=failed"
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Save File
|--------------------------------------------------------------------------
*/

$targetFile =
$departmentFolder .
$fileName;

if (

    move_uploaded_file(

        $_FILES["document"]["tmp_name"],

        $targetFile

    )

) {

    /*
    |--------------------------------------------------------------------------
    | Create Status File
    |--------------------------------------------------------------------------
    */

    $statusDirectory =
    "/var/www/atlas-ai/document_status/";

    if (
        !is_dir($statusDirectory)
    ) {

        mkdir(
            $statusDirectory,
            0775,
            true
        );

    }

    $statusFile =
    $statusDirectory .
    $department .
    "_" .
    $fileName .
    ".txt";

    file_put_contents(
        $statusFile,
        "Pending"
    );

    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    header(
        "Location: documents.php?upload=success"
    );

    exit;

}

/*
|--------------------------------------------------------------------------
| Upload Failed
|--------------------------------------------------------------------------
*/

header(
    "Location: documents.php?upload=failed"
);

exit;

?>
