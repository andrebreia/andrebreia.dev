@extends('_layouts.main', [
    'title' => 'Web developer',
    'description' => 'Web developer specialised in Laravel based in Southampton, UK',
])

@section('content')

    <section class="mt-8 mb-12 md:mb-16 md:mt-12 lg:mt-16 lg:mb-24">
        <div class="container lg:max-w-2xl">
            <div class="flex flex-wrap flex-col md:flex-row-reverse md:-mx-12 items-center">

                <div class="mb-8 md:mb-0 block w-48 md:w-1/2 md:pl-24 max-w-xs md:max-w-full">
                    <img class="block rounded-full shadow-md m-auto" src="/assets/images/andre.jpg" alt=""
                        width="400" height="400">
                </div>

                <div class="text-center md:text-left md:w-1/2 md:px-12">
                    <h2 class="mb-8 text-4xl font-semibold text-white">Web Developer</h2>
                    <p class="mb-4">Hi! My name is André Breia, I'm a Web Developer based in Lyon (France) and currently working at <a
                            class="text-brand-secondary underline" target="_blank"
                            href="https://steadfastcollective.com">Steadfast Collective</a> as a Tech Lead.</p>
                    <p class="mb-4">Over the years I've worked in many web based projects, from small marketing websites to
                        e-commerce websites. These days, I focus primarily on web apps built using the Laravel framework. I
                        also like to work with TailwindCSS and VueJs.</p>
                    <p>I'm always open to chat about dev, feel free to connect with me.</p>
                </div>
            </div>
        </div>
    </section>

@endsection
