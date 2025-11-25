<?php include 'header.php'; ?>

<!-- # menu
    ================================================== -->
<section id="page-menu" class="container s-menu target-section">

    <div class="row s-menu__content">

        <div class="column xl-12">

            <div class="section-header" data-num="01">
                <h2 class="text-display-title">Our Menu</h2>
            </div>

            <?php
            require_once 'includes/json_handler.php';
            $menuHandler = new JsonHandler('data/menu.json');
            $menuItems = $menuHandler->read();

            // Group by category
            $menuByCategory = [];
            foreach ($menuItems as $item) {
                $menuByCategory[$item['category']][] = $item;
            }
            ?>

            <div class="menu-block">

                <?php if (empty($menuByCategory)): ?>
                    <p>No menu items available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($menuByCategory as $category => $items): ?>
                        <h3 class="menu-block__cat-name"><?php echo htmlspecialchars($category); ?></h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>Price (PHP)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="menu-item-header">
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                            class="menu-item-image">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                        <?php if (isset($item['is_featured']) && $item['is_featured']): ?>
                                                            <br><small>(Signature)</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                                            <td><?php echo number_format($item['price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div> <!-- menu-block -->

        </div> <!-- end column -->

    </div> <!-- end s-menu__content -->

</section> <!-- end s-menu -->

<?php include 'footer.php'; ?>