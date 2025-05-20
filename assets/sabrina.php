<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes des étudiants</title>
    <!-- Lien vers Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Notes des Étudiants</h2>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $notes_etudiants = array(
                    "Mohamed" => "16", 
                    "Ahmed" => "14", 
                    "Rafika" => "13",
                    "Aicha" => "15", 
                    "Samir" => "13", 
                    "ALI" => "13", 
                    "Rafik" => "10", 
                    "Fateh" => "09",
                    "Younes" => "07", 
                    "Sami" => "07", 
                    "Islem" => "14"
                );

                foreach ($notes_etudiants as $nom => $note) {
                    echo "<tr>
                            <td>{$nom}</td>
                            <td>{$note}</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Lien vers Bootstrap JS et jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
