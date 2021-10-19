<?php
require_once(dirname(__FILE__) . "/../../init.php");

function vardump($var)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

require_once(dirname(__FILE__) . "/../processor.php");

if (!empty($_FILES['Image'])) {
    $path = $_FILES['Image']['tmp_name'];

    $fileSys = new FileSys();
    $fileData = $fileSys->GetFileContent($path);

    $tmpFilePath = PROJECT_DIR . "var/log/" . $_FILES['Image']['name'];
    $fileSys->PutFileContent($tmpFilePath, $fileData);

    if (file_exists($tmpFilePath)) {
        $processor = new OCRProcesor();
        echo "OCR URL: " . $processor->CheckUrl();
        $result = $processor->process($tmpFilePath);

        if ($result->status == "fail") {
            vardump("OCR failed to check file. Request data: ");
            vardump($result->requestData);
        } elseif ($result->status == "error") {
            vardump("Error occurred. Error code: ");
            vardump($result->errorCode);
        } elseif ($result->status == "success") {
            vardump("OCR successfully got an image.");
        }
    } else {
        echo "There was an error uploading the file, please try again!";
    }

    @unlink($tmpFilePath);
}

?>

    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            * {
                box-sizing: border-box;
            }

            .block {
                border: 1px solid black;
                padding: 10px;
            }

            .column {
                float: left;
                width: 50%;
                padding: 10px;
                overflow: auto;
            }

            .row:after {
                content: "";
                display: table;
                clear: both;
            }
        </style>
    </head>
    <body>
    <div class="block">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="Image"/>
            <input type="submit" value="Process"/>
        </form>
    </div>
    <div class="row">
        <div class="column">
            <h3>OCR text result</h3>
            <?php if (isset($result)) {
                echo nl2br($result->text);
            } ?>
        </div>
        <div class="column">
            <h3>Structure</h3>
            <?php if (isset($result)) { ?>
                <b>Shop:</b>  <?php echo $result->shopTitle; ?><br/>
                <b>Time:</b>  <?php if ($result->dateTime) {
                    echo $result->dateTime->format('Y-m-d H:i:s');
                              } ?><br/>
                <b>VAT:</b>
                <?php
                foreach ($result->VAT as $key => $value) {
                    echo $key . "=" . $value . "%, ";
                }
                ?>
                <br/>
                <table border=1>
                    <thead>
                    <tr>
                        <th>id</th>
                        <th>title</th>
                        <th>price</th>
                        <th>VAT</th>
                        <th>quantity</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($result->products as $product) { ?>
                        <tr>
                            <td><?php echo $product->id; ?></td>
                            <td><?php echo $product->title; ?></td>
                            <td><?php echo $product->price; ?></td>
                            <td><?php echo $product->vat; ?></td>
                            <td><?php echo $product->qty; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
    </body>
    </html>
<?php

?>