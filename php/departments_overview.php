<?php include 'sidebar.php'; ?>

<div class="container" style="padding: 40px;">
    <h2 style="text-align:center; margin-bottom: 30px;">📦 Gérer les Produits par Département</h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">

        <?php
        $departments = [
            ['name' => 'Informatique', 'desc' => 'Programmation, base de données, IA...', 'icon' => '💻'],
            ['name' => 'Mathématiques', 'desc' => 'Algèbre, analyse, statistiques...', 'icon' => '➗'],
            ['name' => 'Biologie', 'desc' => 'Organismes vivants et interactions...', 'icon' => '🧬'],
            ['name' => 'Chimie', 'desc' => 'Transformations de la matière...', 'icon' => '⚗️'],
            ['name' => 'Physique', 'desc' => 'Électromagnétisme, thermodynamique...', 'icon' => '🔬'],
            ['name' => 'Langues', 'desc' => 'Langues étrangères et linguistique...', 'icon' => '🌐'],
        ];

        foreach ($departments as $dep) {
            $name = $dep['name'];
            $desc = $dep['desc'];
            $icon = $dep['icon'];
            echo "
            <div style='background: #f9f9f9; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;'>
                <div style='font-size: 40px; margin-bottom: 10px;'>$icon</div>
                <h3>$name</h3>
                <p style='min-height: 60px;'>$desc</p>
                <a href='admin_products.php?filter=$name'>
                    <button style='padding: 8px 16px; background: #004080; color: white; border: none; border-radius: 5px;'>Gérer</button>
                </a>
            </div>";
        }
        ?>

        <!-- Carte "Gérer Tous" -->
        <div style="background: #dff0d8; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">📁</div>
            <h3>Gérer Tous</h3>
            <p style='min-height: 60px;'>Voir tous les produits de tous les départements.</p>
            <a href="admin_products.php">
                <button style="padding: 8px 16px; background: #3c763d; color: white; border: none; border-radius: 5px;">Gérer Tous</button>
            </a>
        </div>

    </div>
</div>
