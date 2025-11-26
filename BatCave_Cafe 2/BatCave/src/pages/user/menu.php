<?php include '../../components/header.php'; ?>

<!-- # menu
    ================================================== -->
<section id="page-menu" class="container s-menu target-section">

    <div class="row s-menu__content">

        <div class="column xl-12">

            <div class="section-header" data-num="01">
                <h2 class="text-display-title">Our Menu</h2>
            </div>

            <?php
            require_once '../../includes/json_handler.php';
            $menuHandler = new JsonHandler('../../data/menu.json');
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
                        <div class="menu-grid">
                            <?php foreach ($items as $item): ?>
                                <div class="menu-card">
                                    <?php if (!empty($item['image'])): ?>
                                        <div class="menu-card__image-wrapper">
                                            <img src="<?php echo htmlspecialchars('../../' . $item['image']); ?>"
                                                alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-card__image">
                                        </div>
                                    <?php endif; ?>
                                    <div class="menu-card__content">
                                        <div class="menu-card__header">
                                            <h3 class="menu-card__title"><?php echo htmlspecialchars($item['name']); ?></h3>
                                            <?php if (isset($item['is_featured']) && $item['is_featured']): ?>
                                                <span class="menu-card__badge">Signature</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="menu-card__desc"><?php echo htmlspecialchars($item['description']); ?></p>
                                        <div class="menu-card__footer">
                                            <span class="menu-card__price">PHP
                                                <?php echo number_format($item['price'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div> <!-- menu-block -->

        </div> <!-- end column -->

    </div> <!-- end s-menu__content -->

</section> <!-- end s-menu -->

<?php include '../../components/footer.php'; ?>