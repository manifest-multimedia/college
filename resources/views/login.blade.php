<x-backend.auth title="Login" description="Login to your account">
   
  
    
                    <form class="px-4 mx-auto mw-sm">
    
                        <div class="mb-6 row">
    
                            <a href="https://auth.pnmtc.edu.gh/login?redirect_url={{ urlencode(route('auth.callback')) }}"
                                class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                                Login with AuthCentral
                            </a>
    
                        </div>
                        <p class="mb-0 text-center fs-13 fw-medium text-light-dark"> <span>Don't have an account?</span>
                            <a class="text-primary link-primary" href="{{ route('signup') }}">Sign up</a>
                        </p>
                    </form>
                
    

</x-backend.auth>