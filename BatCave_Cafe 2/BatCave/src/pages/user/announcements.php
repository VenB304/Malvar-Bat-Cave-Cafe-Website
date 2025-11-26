<?php include '../../components/header.php'; ?>

<!-- # announcements
    ================================================== -->
<section id="page-announcements" class="container s-announcements target-section">

    <div class="row s-announcements__content">

        <div class="column xl-12">

            <div class="section-header" data-num="01">
                <h2 class="text-display-title">Announcements & Events</h2>
            </div>

            <?php
            require_once '../../includes/json_handler.php';
            $announcementHandler = new JsonHandler('../../data/announcements.json');
            $announcements = $announcementHandler->read();

            // Sort by date descending
            usort($announcements, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            ?>

            <div class="announcement-block">

                <?php if (empty($announcements)): ?>
                    <p>No announcements at the moment. Stay tuned!</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($announcements as $item): ?>
                            <div class="column xl-12 mb-4">
                                <div class="announcement-card"
                                    style="border: 1px solid #eee; padding: 20px; border-radius: 8px; height: 100%;">
                                    <?php if (!empty($item['image'])): ?>
                                        <div class="announcement-image" style="margin-bottom: 15px;">
                                            <img src="<?php echo htmlspecialchars('../../' . $item['image']); ?>"
                                                alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                style="width: 100%; height: 250px; object-fit: cover; border-radius: 4px;">
                                        </div>
                                    <?php endif; ?>

                                    <div class="announcement-meta" style="color: #888; font-size: 0.9em; margin-bottom: 10px;">
                                        <?php
                                        $date = date_create($item['date']);
                                        echo date_format($date, "F j, Y");
                                        ?>
                                    </div>

                                    <h3 class="h4 announcement-title" style="margin-bottom: 15px;">
                                        <?php echo htmlspecialchars($item['title']); ?></h3>

                                    <div class="announcement-content">
                                        <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div> <!-- announcement-block -->

        </div> <!-- end column -->

    </div> <!-- end s-announcements__content -->

</section> <!-- end s-announcements -->

<?php include '../../components/footer.php'; ?>