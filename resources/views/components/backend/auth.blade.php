<!DOCTYPE html>
<html lang="en">

<head>
    <title>AuthCentral - {{ $title }} </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="shuffle-for-bootstrap.png">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            overflow-y: auto; /* Allow vertical scrolling */
        }
        
        /* Custom scrollbar styling - vertical only */
        ::-webkit-scrollbar {
            width: 6px;
            height: 0; /* Remove horizontal scrollbar */
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Form container styles */
        .auth-form-container {
            width: 100%;
            padding: 1rem 0;
        }
        
        /* Make sure content doesn't overflow horizontally */
        form, .form-group, .row {
            max-width: 100%;
            word-wrap: break-word;
        }
        
        /* Responsive adjustments */
        @media (max-height: 800px) {
            .auth-logo {
                height: 100px !important;
                margin-bottom: 0.5rem;
            }
            
            .mb-7 {
                margin-bottom: 1rem !important;
            }
        }
        
        /* Main page container */
        .main-wrapper {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
        }
        
        /* Testimonial side - full height background */
        .testimonial-bg {
            position: fixed;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background-color: #f8f9fa;
            z-index: 0;
        }
        
        /* Content layout */
        .content-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            min-height: 100vh;
        }
        
        /* Form side centered vertically, just like testimonials */
        .form-side {
            width: 50%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        
        .form-content {
            max-width: 90%;
            width: 450px; /* Match the testimonial content width */
        }
        
        .testimonial-side {
            width: 50%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        /* Mobile responsive styles */
        @media (max-width: 767.98px) {
            .testimonial-bg {
                display: none; /* Hide fixed background on mobile */
            }
            
            .form-side,
            .testimonial-side {
                width: 100%;
            }
            
            .testimonial-side {
                background-color: #f8f9fa;
            }
            
            .form-content {
                max-width: 100%;
                width: 100%;
                padding: 0 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="main-wrapper" style="background-image: url('{{ asset('images/pattern-light.png') }}')">
        <!-- Fixed background for testimonials side -->
        <div class="testimonial-bg"></div>
        
        <!-- Gradient overlay -->
        <div class="top-0 position-absolute start-0 h-100 w-100"
            style="background: radial-gradient(50% 50% at 50% 50%, rgba(255, 255, 255, 0) 0%, #FFFFFF 100%); z-index: 0;"></div>
            
        <div class="content-wrapper">
            <div class="form-side">
                <div class="form-content">
                    <div class="text-center">
                        <img class="img-fluid auth-logo" style="height: 150px;" src="{{ asset('images/pnmtc-logo.png') }}"
                            alt="">
                        <h2 class="mb-4 font-heading fs-7">{{ $description }}</h2>
                        
                        <!-- Error messages moved to individual pages -->
                    </div>
                    <div class="auth-form-container">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            
            <div class="testimonial-side">
                <div class="mx-auto text-center mw-md-xl quotes">
                    <span class="mb-4 shadow badge bg-primary-dark text-primary text-uppercase">TESTIMONIALS</span>
                    <div class="mb-20 position-relative">
                        <h2 class="position-relative font-heading fs-7 fw-medium text-light-dark"
                            style="z-index: 1;">Love the simplicity of the service and the prompt customer
                            support. We can't imagine working without it.</h2>
                        <img class="top-0 position-absolute start-0 ms-n12 mt-n10"
                            src="flex-assets/images/sign-in/quote-top.svg" alt="">
                        <img class="bottom-0 position-absolute end-0 me-n10 mb-n16"
                            src="flex-assets/images/sign-in/quote-down.svg" alt="">
                    </div>
                    <img class="mb-6 img-fluid" src="flex-assets/images/sign-in/avatar.png" alt="">
                    <h3 class="mb-1 font-heading fs-10 fw-semibold text-light-dark">John Doe</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        // Initialize Bootstrap components including alert dismissal
        document.addEventListener('DOMContentLoaded', function() {
            // Enable all dismissible alerts
            var alertList = document.querySelectorAll('.alert-dismissible');
            alertList.forEach(function(alert) {
                new bootstrap.Alert(alert);
            });
        });
        
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

    <script src="js/main.js"></script>
</body>

</html>
