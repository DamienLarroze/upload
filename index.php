<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    #Fonction générant un identifiant unique pour chaque fichier
    function uniqidReal($lenght = 38)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new Exception("Pas de sécurité !");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }

    #Configuration
    $uniqueId = uniqidReal();
    $maxFileSize = 1000000;
    $authorizedExtensions = ['jpg', 'jpeg', 'png'];
    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $uploadDir = 'files/';
    $fileWidth = $_FILES['avatar']['size'];
    $fileName = $_FILES['avatar']['name'];
    $fileTmp = $_FILES['avatar']['tmp_name'];

    #Check
    if (!in_array($extension, $authorizedExtensions)) {
        $errors[] = "Ce n'est pas une extension valide !";
    } else {
        #Modification du nom du fichier pour l'upload
        $uploadFile = $uploadDir . basename($uniqueId . "." . $extension);
    }

    if (!file_exists($fileTmp)) {
        $errors[] = "L'image n'existe pas";
    } elseif (filesize($fileWidth > $maxFileSize)) {
        $errors[] = "Le poids de l'image est trop grande !";
    } elseif (!file_exists($uploadFile)) {
        #Upload
        try {
            move_uploaded_file($fileTmp, $uploadFile);
            echo "Votre fichier à été ajouté, voici un aperçu :<br>";
            echo "<img src='$uploadFile' style='width:50%;'><br><br>";
        } catch (Exception $e) {
            echo 'Erreur : ', $e->getMessage(), "\n";
        }
    }

    if (isset($_POST['delete'])) {
        unlink($_POST['filename']);
        $valide = "Votre fichier à bien été supprimé !";
    }
}
?>

<form action="#" method="post" enctype="multipart/form-data">
    <input type="file" name="avatar" id="imageUpload" /><br><br><br>
    <button name="send">Envoyer</button>
</form>

<?php
echo "<h1>Liste des fichiers sur le serveur :</h1> <br><br>";

if (isset($valide)) {
    echo "<h3>" . $valide . "</h3>";
}

$fileList = glob('files/*');

foreach ($fileList as $filename) {
    if (is_file($filename)) {
        echo "<br><br>Nom du fichier : <p style='color:red;'>" . $filename, '</p><br>';
        echo "Aperçu : <br><br>
        <img src='$filename' style='width:30%'>";
?>
        <form action="#" method="post">
            <input type="hidden" name="filename" value="<?= $filename ?>">
            <button name="delete">Supprimer</button>
        </form>
<?php
    }
}
?>