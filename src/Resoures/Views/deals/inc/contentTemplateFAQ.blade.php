

<div class="aside-box faq-template">
    <h3 class="aside-title widget-title faqs-title">
        FAQ for {{ $keyword['keyword'] }} {{ date('M Y') }}
    </h3>
    <div class="mb-content" id="faq-content-template">
        <?= $contentTemplateFAQ ?>
        <div class="faq-title feedback-link">
            <a rel="nofollow" href="<?= route('frontend::home::feedback') ?>">Feedback</a>
        </div>
    </div>
    
</div>

<script>
    let faqElements = document.querySelectorAll('#faq-content-template .faq-title');
    for (let item of faqElements) {
        item.addEventListener('click', function () {
            item.classList.toggle('active');
        })
    }
</script>
