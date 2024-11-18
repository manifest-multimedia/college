<!DOCTYPE html>
<html lang="en">

<head>
    <title>PNMTC College System - Login </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/bootstrap/bootstrap.min.css') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="shuffle-for-bootstrap.png">
</head>

<body>


    <section data-from-ai="true" class="overflow-hidden pt-20 bg-white vh-100 position-relative pt-md-0"
        style="background-image: url('{{ asset('images/pattern-light.png') }}')">
        <div class="top-0 position-absolute start-0 h-100 w-100"
            style="background: radial-gradient(50% 50% at 50% 50%, rgba(255, 255, 255, 0) 0%, #FFFFFF 100%);"></div>
        <div class="position-relative row align-items-center g-16 h-100" style="z-index:1;">
            <div class="col-12 col-md-6">
                <div class="px-4 mx-auto mb-7 text-center mw-md">

                    <img class="img-fluid" style="height: 150px;" src="{{ asset('images/pnmtc-logo.png') }}"
                        alt="">
                    <h2 class="mb-4 font-heading fs-7">Sign in to your account</h2>
                </div>
                <form class="px-4 mx-auto mw-sm">

                    <div class="mb-6 row">

                        <a href="http://auth.local/login?redirect_url={{ urlencode(route('auth.callback')) }}"
                            class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                            Login with AuthCentral
                        </a>

                    </div>
                    <p class="mb-0 text-center fs-13 fw-medium text-light-dark"> <span>Don't have an account?</span>
                        <a class="text-primary link-primary" href="{{ route('register') }}">Sign up</a>
                    </p>
                </form>
            </div>
            <div class="col-12 col-md-6 h-100">
                <div
                    class="px-4 py-36 bg-light-light h-100 d-flex flex-column align-items-center justify-content-center">
                    <div class="mx-auto text-center mw-md-xl quotes"> <span
                            class="mb-4 shadow badge bg-primary-dark text-primary text-uppercase">Testimonials</span>
                        <div class="mb-20 position-relative">
                            <h2 class="position-relative font-heading fs-7 fw-medium text-light-dark"
                                style="z-index: 1;">Love the simplicity of the service and the prompt customer
                                support. We can't imagine working without it.</h2> <img
                                class="top-0 position-absolute start-0 ms-n12 mt-n10"
                                src="flex-assets/images/sign-in/quote-top.svg" alt=""> <img
                                class="bottom-0 position-absolute end-0 me-n10 mb-n16"
                                src="flex-assets/images/sign-in/quote-down.svg" alt="">
                        </div> <img class="mb-6 img-fluid" src="flex-assets/images/sign-in/avatar.png" alt="">
                        <h3 class="mb-1 font-heading fs-10 fw-semibold text-light-dark">John Doe</h3>

                    </div>
                </div>
            </div>
        </div>
    </section>


    <script>
        // Sample quotes data array
        const quoteData = [{
                text: "I have worked with Manifest Digital for 2+ years and I like how they work with speed. Their works are neat with beautiful interfaces. Thanks for making Yve Digital & Get The Artiste platforms what they are.",
                author: "Kwame Baah, CEO & Founder at Yve Digital (GH)"
            },
            {
                text: "Having worked with manifest Ghana, I will recommend them to any client. They are professional, time-efficient and productivity is of a high standard.",
                author: "Esther Yeboah, CEO at Eunson Consulting (UK)"
            },
            {
                text: "I have given them five stars for the excellent work done. I recommend them to anyone or organization that needs a fast, efficient, and reliable website for any purpose.",
                author: "Mawuli Nyador (GH) "
            }
        ];

        let index = 0;
        const slideTime = 5000; // 5 seconds interval for auto-slide
        let autoSlideInterval;

        const quotes = document.querySelector('.quotes');
        const quoteText = quotes.querySelector('h2');
        const quoteAuthor = quotes.querySelector('h3');
        const dotsContainer = document.createElement('div');

        dotsContainer.classList.add('row', 'justify-content-center', 'g-3', 'mt-4');

        // Create dots dynamically
        quoteData.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.classList.add('col-auto');
            dot.innerHTML =
                `<a class="d-inline-block rounded-pill bg-light" style="width: 12px; height: 12px; cursor: pointer;" href="#"></a>`;
            dot.querySelector('a').addEventListener('click', () => {
                navigateToQuote(i);
            });
            dotsContainer.appendChild(dot);
        });

        quotes.appendChild(dotsContainer);
        const dots = dotsContainer.querySelectorAll('a');

        // Function to update quote text and author
        function updateQuote() {
            quoteText.innerText = quoteData[index].text;
            quoteAuthor.innerText = quoteData[index].author;

            // Update dots (previous, current, and next)
            dots.forEach((dot, i) => {
                dot.classList.remove('bg-primary', 'bg-light');
                if (i === index) {
                    dot.classList.add('bg-primary'); // Current dot is green
                } else {
                    dot.classList.add('bg-light'); // Other dots are light
                }
            });
        }

        // Auto slide function
        function autoSlide() {
            index = (index + 1) % quoteData.length;
            updateQuote();
        }

        // Navigate to a specific quote
        function navigateToQuote(i) {
            index = i;
            updateQuote();
            clearInterval(autoSlideInterval); // Stop auto-slide on manual navigation
            autoSlideInterval = setInterval(autoSlide, slideTime); // Restart auto-slide
        }

        // Initialize first quote and auto-slide
        updateQuote();
        autoSlideInterval = setInterval(autoSlide, slideTime);
    </script>






    <script src="js/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>
