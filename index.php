<?php include 'header.php'; ?>

<!-- # intro
        ================================================== -->
<section id="intro" class="container s-intro target-section">

    <div class="grid-block s-intro__content">

        <div class="intro-header">
            <div class="intro-header__overline">Welcome to</div>
            <h1 class="intro-header__big-type">
                The Malvar <br>
                Bat Cave Cafe
            </h1>
        </div> <!-- end intro-header -->

        <figure class="intro-pic-primary">
            <img src="images/intro-pic-primary.jpg" srcset="images/intro-pic-primary.jpg 1x, 
                         images/intro-pic-primary@2x.jpg 2x" alt="">
        </figure> <!-- end intro-pic-primary -->

        <div class="intro-block-content">

            <figure class="intro-block-content__pic">
                <img src="images/intro-pic-secondary.jpg" srcset="images/intro-pic-secondary.jpg 1x, 
                             images/intro-pic-secondary@2x.jpg 2x" alt="">
            </figure> <!-- end intro-pic-secondary -->

            <div class="intro-block-content__text-wrap">
                <p class="intro-block-content__text">
                    Your late-night sanctuary for academic success and genuine connection.
                    Open from 1:00 PM to 1:00 AM.
                </p>

                <ul class="intro-block-content__social">
                    <li><a href="#0">FB</a></li>
                    <li><a href="#0">IG</a></li>
                </ul>
            </div> <!-- end intro-block-content__social -->

        </div> <!-- end intro-block-content -->



    </div> <!-- grid-block -->

</section> <!-- end s-intro -->


<!-- # about
        ================================================== -->
<section id="about" class="container s-about target-section">

    <div class="row s-about__content">

        <div class="column xl-4 lg-5 md-12 s-about__content-start">

            <div class="section-header" data-num="01">
                <h2 class="text-display-title">Our Mission</h2>
            </div>

            <figure class="about-pic-primary">
                <img src="images/about-pic-primary.jpg" srcset="images/about-pic-primary.jpg 1x, 
                             images/about-pic-primary@2x.jpg 2x" alt="">
            </figure>

        </div> <!-- end s-about__content-start -->

        <div class="column xl-6 lg-6 md-12 s-about__content-end">
            <p>
                The Malvar Bat Cave Cafe is dedicated to providing a consistently comfortable, secure, and inspiring
                environment where students can focus and socialize. We commit to serving high-quality coffee and
                nourishment, and offering a seamless, professional experience through functional services like our
                dedicated reservation system, ensuring every visit lights up the path to their next achievement.
            </p>

            <h3>Our Vision</h3>
            <p>
                To be the undisputed sanctuary and second home for the BSU community, recognized as the best late-night
                establishment that fuels academic success, fosters genuine connection, and elevates the local coffee
                culture in Malvar.
            </p>

        </div> <!--end column -->

    </div> <!-- end s-about__content-end -->

</section> <!-- end s-about -->


<!-- # menu
        ================================================== -->
<section id="menu" class="container s-menu target-section">

    <div class="row s-menu__content">

        <div class="column xl-4 lg-5 md-12 s-menu__content-start">

            <div class="section-header" data-num="02">
                <h2 class="text-display-title">Featured Items</h2>
            </div>

            <a href="menu.php" class="btn btn--primary h-full-width">View Full Menu</a>

        </div> <!-- end s-menu__content-start -->

        <div class="column xl-6 lg-6 md-12 s-menu__content-end">

            <div class="row s-menu__content">
                <div class="column xl-12">
                    <div class="menu-block">
                        <?php
                        require_once 'includes/json_handler.php';
                        $menuHandler = new JsonHandler('data/menu.json');
                        $menuItems = $menuHandler->read();
                        $featuredItems = array_filter($menuItems, function ($item) {
                            return isset($item['is_featured']) && $item['is_featured'];
                        });
                        ?>

                        <?php if (empty($featuredItems)): ?>
                            <p>No featured items available at the moment.</p>
                        <?php else: ?>
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
                                        <?php foreach ($featuredItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div style="display:flex; align-items:center; gap:1rem;">
                                                        <?php if (!empty($item['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                                style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                                                        <?php endif; ?>
                                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                                <td><?php echo number_format($item['price'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div> <!-- end s-menu__content-end -->

    </div> <!-- end s-menu__content -->

</section> <!-- end s-menu -->

<?php include 'footer.php'; ?>