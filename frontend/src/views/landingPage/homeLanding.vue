<template>
  <!--
    `isolate` so the backdrop's negative z-index is resolved against this element
    rather than escaping to some ancestor further up, and so the tutorial overlays
    teleported to <body> still paint above everything here.
  -->
  <div
    id="top"
    class="relative isolate min-h-screen bg-[#020617] font-sans text-slate-300 antialiased selection:bg-cyan-400/30 selection:text-white"
  >
    <!--
      One fixed backdrop for the whole page rather than a glow per section. Per-section
      orbs each carried their own stacking and their own blur radius, which compounded
      on long pages and made the seams visible while scrolling.
    -->
    <div aria-hidden="true" class="landing-backdrop pointer-events-none fixed inset-0 -z-10 overflow-hidden">
      <div class="absolute -left-48 top-[-12%] h-[36rem] w-[36rem] rounded-full bg-cyan-600/[0.12] blur-[130px]"></div>
      <div class="absolute -right-48 top-[28%] h-[32rem] w-[32rem] rounded-full bg-violet-600/[0.12] blur-[130px]"></div>
      <div class="absolute bottom-[-10%] left-1/4 h-[30rem] w-[30rem] rounded-full bg-sky-500/[0.08] blur-[130px]"></div>
      <div class="landing-grid absolute inset-0"></div>
    </div>

    <!-- ── Hero ────────────────────────────────────────────────────────────── -->
    <!--
      The headline, its paragraph and the primary actions sit here, at the top, so a
      visitor knows what the site is on arrival. The words are unchanged; they are just
      no longer in a statement block halfway down the page.

      This replaced a Spline WebGL scene. A decorative 3D canvas cost a third-party
      script from a CDN, a full-screen render loop, and a scroll handler whose only
      job was forwarding wheel events the canvas had swallowed -- none of which
      earned their keep on a page selling a business system. The depth on the right
      is CSS 3D instead: perspective, a tilted stack of plates and a floating pane,
      composited by the browser's own renderer. No dependency, no canvas, no main
      thread work.
    -->
    <section class="relative isolate flex min-h-[92vh] items-center overflow-hidden pt-20 lg:pt-0">
      <div class="mx-auto grid w-full max-w-7xl items-center gap-16 px-6 py-20 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-28">
        <div class="max-w-2xl">
          <p
            class="inline-flex items-center gap-2 rounded-full border border-cyan-400/25 bg-cyan-400/[0.07] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-cyan-300"
          >
            <span class="relative flex h-1.5 w-1.5">
              <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>
              <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
            </span>
            Cavite Paint IMS
          </p>

          <h1
            class="mt-6 text-4xl font-extrabold leading-[1.05] tracking-[-0.03em] text-white sm:text-5xl lg:text-7xl"
          >
            Integrated<br />
            <span class="bg-gradient-to-r from-cyan-300 via-sky-300 to-violet-300 bg-clip-text text-transparent">Management System</span>
          </h1>

          <p class="mt-6 max-w-2xl text-base leading-relaxed text-slate-400 sm:text-lg">
            A centralized, high-performance platform for paint distributors and service providers in Cavite,
            seamlessly integrating 12 modules into one unified ecosystem.
          </p>

          <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
            <button
              type="button"
              @click="$router.push('/virtual-mixing')"
              class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-cyan-400 px-6 py-3.5 text-sm font-semibold text-slate-950 shadow-lg shadow-cyan-500/20 transition-all hover:bg-cyan-300 hover:shadow-cyan-400/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            >
              <img src="/favicon.svg" alt="" class="h-5 w-5" />
              Virtual Mix
            </button>

            <a
              href="https://median.co/share/eenpqek#apk"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center justify-center gap-2.5 rounded-xl border border-white/10 bg-white/[0.04] px-6 py-3.5 text-sm font-semibold text-slate-200 transition-all hover:border-white/20 hover:bg-white/[0.08] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                />
              </svg>
              Download APK
            </a>
          </div>
        </div>

        <!--
          The 3D half. Perspective is set on the stage, the tilt on the plate stack
          inside it, so the two `translateZ` layers -- the shadowed back plate and the
          floating front pane -- resolve as real depth against one vanishing point
          rather than as stacked flat rectangles. Every layer is decorative, so the
          whole assembly is aria-hidden and the browser skips it for assistive tech.
        -->
        <div class="hero-stage relative mx-auto w-full max-w-[26rem] lg:max-w-none">
          <!-- Colour bleeding out from behind the stack, so the tilt reads against something. -->
          <div
            aria-hidden="true"
            class="absolute inset-8 -z-10 rounded-full bg-cyan-500/20 blur-[90px]"
          ></div>

          <div class="hero-tilt relative aspect-[4/5] w-full">
            <!--
              Back plate. Sits behind and slightly larger than the photograph, so the
              gap between the two is what sells the depth.
            -->
            <div
              aria-hidden="true"
              class="hero-plate-back absolute -inset-4 rounded-[2rem] border border-white/[0.06] bg-white/[0.015]"
            ></div>

            <!-- The photograph itself, on the middle layer. -->
            <div class="hero-plate absolute inset-0 overflow-hidden rounded-[1.75rem] border border-white/10">
              <!--
                `fetchpriority="high"` because this is the largest thing on screen at
                load, so it is what the browser treats as the LCP element -- it should
                not be queued behind the bundle or the stylesheet.

                The `object-position` bias is not decoration. This photograph is 2:3
                and roughly the top 40% of it is empty sky. Centre-cropping it into
                the 4:5 plate would hand about a third of the panel to blank white, so
                the crop is pushed down onto the buildings.
              -->
              <img
                src="/bg01.jpg"
                alt=""
                aria-hidden="true"
                fetchpriority="high"
                class="h-full w-full object-cover object-[center_75%]"
              />
              <!--
                Two overlays, and both exist because this image is high-key where the
                one it replaces was dark. A flat pass knocks its overall luminance down
                so it does not outshine the headline; the vignette then anchors the
                bottom edge of the plate and darkens the sky at the top, turning the
                brightest part of the photograph into the panel's quietest.
              -->
              <div aria-hidden="true" class="absolute inset-0 bg-[#020617]/20"></div>
              <div
                aria-hidden="true"
                class="absolute inset-0 bg-gradient-to-t from-[#020617] via-[#020617]/20 to-[#020617]/75"
              ></div>
            </div>

            <!--
              Front pane, floating clear of the photograph at the nearest depth. Two
              overlapping colour discs rather than any label: it gestures at the
              colour-mixing feature without claiming to be a screenshot of it.
            -->
            <div
              aria-hidden="true"
              class="hero-pane-front absolute -bottom-8 -left-8 flex h-28 w-44 items-center justify-center gap-3 rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur-md"
            >
              <span class="h-12 w-12 rounded-full bg-cyan-400/70 blur-[1px]"></span>
              <span class="h-12 w-12 rounded-full bg-violet-400/70 blur-[1px]"></span>
              <span class="h-12 w-12 rounded-full bg-sky-300/50 blur-[1px]"></span>
            </div>

            <!-- A second pane on the far edge, at the deepest readable depth. -->
            <div
              aria-hidden="true"
              class="hero-pane-back absolute -right-6 -top-6 h-24 w-24 rounded-2xl border border-white/[0.08] bg-white/[0.04] backdrop-blur-md"
            ></div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Companion platforms ─────────────────────────────────────────────── -->
    <section class="mx-auto w-full max-w-7xl px-6 py-20 lg:px-8 lg:py-24">
      <div class="grid gap-5 md:grid-cols-2">
        <!-- Mobile App -->
        <article
          class="group relative overflow-hidden rounded-2xl border border-white/[0.07] bg-white/[0.02] p-8 transition-colors hover:border-violet-400/30 hover:bg-white/[0.035] lg:p-10"
        >
          <img
            src="/mobile-app.jpg"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-full object-cover opacity-[0.07] transition-all duration-700 group-hover:scale-105 group-hover:opacity-[0.12]"
          />
          <div class="relative">
            <div
              class="mb-7 grid h-12 w-12 place-items-center rounded-xl border border-violet-400/20 bg-violet-400/10 text-violet-300 transition-colors group-hover:bg-violet-400/20"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.75"
                  d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                />
              </svg>
            </div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-violet-300">Mobile App</p>
            <h3 class="mt-2 text-2xl font-bold tracking-tight text-white lg:text-[1.75rem]">On-The-Go Access</h3>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-400">
              Android companion app for field operations & real-time updates.
            </p>
            <a
              href="https://median.co/share/eenpqek#apk"
              target="_blank"
              rel="noopener noreferrer"
              class="mt-7 inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.04] px-5 py-2.5 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-200 transition-colors hover:border-violet-400/40 hover:bg-violet-400/10 hover:text-white"
            >
              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                />
              </svg>
              Download APK
            </a>
          </div>
        </article>

        <!-- DSS -->
        <article
          class="group relative overflow-hidden rounded-2xl border border-white/[0.07] bg-white/[0.02] p-8 transition-colors hover:border-sky-400/30 hover:bg-white/[0.035] lg:p-10"
        >
          <img
            src="/dss-analytics.jpg"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-full object-cover opacity-[0.07] transition-all duration-700 group-hover:scale-105 group-hover:opacity-[0.12]"
          />
          <div class="relative">
            <div
              class="mb-7 grid h-12 w-12 place-items-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-300 transition-colors group-hover:bg-sky-400/20"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.75"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
              </svg>
            </div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-sky-300">DSS Module</p>
            <h3 class="mt-2 text-2xl font-bold tracking-tight text-white lg:text-[1.75rem]">Decision Support</h3>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-slate-400">
              AI-driven analytics & forecasting for Cavite market trends.
            </p>
          </div>
        </article>
      </div>
    </section>

    <!-- ── Modules ─────────────────────────────────────────────────────────── -->
    <!--
      One section, because the page used to name the same modules three times: a chip
      row in the hero, these described cards, and a separate twelve-icon strip at the
      foot of the page. The strip is now nested directly under the cards as a compact
      index, so it reads as the same list at a glance rather than as a second pitch --
      and it is still the only place five of the twelve appear at all.
    -->
    <section id="modules" class="relative isolate mx-auto w-full max-w-7xl scroll-mt-20 px-6 py-20 lg:px-8 lg:py-24">
      <div class="max-w-2xl">
        <h2 class="text-3xl font-bold tracking-tight text-white lg:text-4xl">Modules</h2>
      </div>

      <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <article
          v-for="(mod, i) in businessModules"
          :key="i"
          class="group relative flex min-h-[19rem] flex-col justify-between overflow-hidden rounded-2xl border border-white/[0.07] bg-white/[0.02] p-7 transition-all duration-300 hover:-translate-y-1 hover:border-white/15 hover:bg-white/[0.035] focus-within:border-white/15"
        >
          <!--
            The image is decorative, so it sits under a scrim and is hidden from
            assistive tech. It used to be a rounded blob bleeding off two edges, which
            clipped awkwardly at every card width.
          -->
          <img
            :src="mod.img"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-[0.10]"
          />

          <div class="relative">
            <div class="mb-6 flex items-start justify-between gap-3">
              <span :class="mod.textColor" class="text-[11px] font-semibold uppercase tracking-[0.16em]">
                {{ mod.tag }}
              </span>
              <span :class="mod.dotColor" class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"></span>
            </div>
            <h3 class="text-xl font-semibold tracking-tight text-white">{{ mod.title }}</h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ mod.desc }}</p>
          </div>

          <div class="relative mt-8">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.04] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-300 transition-colors hover:border-white/20 hover:bg-white/[0.08] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            >
              {{ mod.action }}
              <svg
                class="h-3 w-3 transition-transform group-hover:translate-x-0.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </article>
      </div>

      <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="(mod, i) in extraModules"
          :key="i"
          class="group relative flex min-h-[19rem] flex-col justify-between overflow-hidden rounded-2xl border border-white/[0.07] bg-white/[0.02] p-7 transition-all duration-300 hover:-translate-y-1 hover:border-white/15 hover:bg-white/[0.035] focus-within:border-white/15"
        >
          <img
            :src="mod.img"
            alt=""
            aria-hidden="true"
            class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-[0.10]"
          />

          <div class="relative">
            <div class="mb-6 flex items-start justify-between gap-3">
              <span :class="mod.textColor" class="text-[11px] font-semibold uppercase tracking-[0.16em]">
                {{ mod.tag }}
              </span>
            </div>
            <h3 class="text-xl font-semibold tracking-tight text-white">{{ mod.title }}</h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ mod.desc }}</p>
          </div>

          <div class="relative mt-8">
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.04] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-300 transition-colors hover:border-white/20 hover:bg-white/[0.08] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            >
              {{ mod.action }}
              <svg
                class="h-3 w-3 transition-transform group-hover:translate-x-0.5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </article>
      </div>

      <!--
        The full capability index, nested inside the section it belongs to. It sits
        directly under the cards and shares their width and radius, so the two are
        read as one list at two depths rather than as two separate sections.
      -->
      <div class="mt-5 rounded-2xl border border-white/[0.06] bg-white/[0.015] px-6 py-9 sm:px-8">
        <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-6">
          <div v-for="(feature, i) in systemFeatures" :key="i" class="group flex flex-col items-center text-center">
            <div
              class="mb-3 grid h-11 w-11 place-items-center rounded-xl border border-white/[0.08] bg-white/[0.03] text-slate-500 transition-all duration-300 group-hover:-translate-y-0.5 group-hover:border-cyan-400/30 group-hover:bg-cyan-400/10 group-hover:text-cyan-300"
            >
              <component :is="feature.icon" class="h-5 w-5" />
            </div>
            <span
              class="text-[10px] font-medium uppercase leading-tight tracking-[0.1em] text-slate-500 transition-colors group-hover:text-cyan-300"
            >
              {{ feature.name }}
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Tutorials & FAQ ─────────────────────────────────────────────────── -->
    <section id="faq" class="relative mx-auto w-full max-w-7xl scroll-mt-20 px-6 py-20 lg:px-8 lg:py-24">
      <div class="mx-auto max-w-3xl text-center">
        <p
          class="inline-flex items-center rounded-full border border-cyan-400/25 bg-cyan-400/[0.07] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-cyan-300"
        >
          Knowledge Base
        </p>
        <h2 class="mt-5 text-3xl font-bold tracking-tight text-white lg:text-4xl">System FAQ</h2>
        <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-slate-400 lg:text-base">
          Everything you need to know about the system features, products, and support procedures.
        </p>
      </div>

      <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
        <button
          type="button"
          @click="openTutorial('client')"
          class="inline-flex items-center justify-center gap-2.5 rounded-xl border border-cyan-400/25 bg-cyan-400/[0.07] px-6 py-3.5 text-sm font-semibold text-cyan-200 transition-all hover:border-cyan-400/50 hover:bg-cyan-400/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
        >
          <PlayCircle class="h-5 w-5" aria-hidden="true" />
          How to Book a Service (Client)
        </button>
        <button
          type="button"
          @click="openTutorial('sp')"
          class="inline-flex items-center justify-center gap-2.5 rounded-xl border border-violet-400/25 bg-violet-400/[0.07] px-6 py-3.5 text-sm font-semibold text-violet-200 transition-all hover:border-violet-400/50 hover:bg-violet-400/15 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
        >
          <PlayCircle class="h-5 w-5" aria-hidden="true" />
          How to Offer Services (Provider)
        </button>
      </div>

      <div class="mx-auto mt-10 max-w-3xl divide-y divide-white/[0.06] overflow-hidden rounded-2xl border border-white/[0.07] bg-white/[0.02]">
        <div v-for="(faq, i) in faqs" :key="i">
          <h3>
            <button
              type="button"
              :id="`faq-trigger-${i}`"
              :aria-expanded="faq.open"
              :aria-controls="`faq-panel-${i}`"
              @click="toggleFaq(i)"
              class="group flex w-full items-center gap-4 px-5 py-5 text-left transition-colors hover:bg-white/[0.03] focus-visible:outline focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-cyan-300 sm:px-7"
            >
              <span
                class="grid h-9 w-9 shrink-0 place-items-center rounded-lg border border-white/[0.08] bg-white/[0.03] text-slate-400 transition-colors group-hover:border-cyan-400/30 group-hover:text-cyan-300"
                :class="faq.open ? 'border-cyan-400/30 text-cyan-300' : ''"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" v-html="faq.icon"></svg>
              </span>
              <span
                class="flex-1 text-sm font-medium leading-snug transition-colors sm:text-[0.95rem]"
                :class="faq.open ? 'text-cyan-200' : 'text-slate-200 group-hover:text-white'"
              >
                {{ faq.question }}
              </span>
              <span
                class="grid h-7 w-7 shrink-0 place-items-center rounded-full border border-white/10 text-slate-500 transition-all duration-300 group-hover:border-white/25 group-hover:text-slate-300"
                :class="faq.open ? 'rotate-180 border-cyan-400/40 bg-cyan-400/10 text-cyan-300' : ''"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
              </span>
            </button>
          </h3>

          <!--
            Animated with a grid-row rather than v-show, so the panel opens to its
            natural height instead of snapping. Hidden is kept on the wrapper so the
            collapsed answer is out of the accessibility tree, not merely invisible.
          -->
          <div
            :id="`faq-panel-${i}`"
            role="region"
            :aria-labelledby="`faq-trigger-${i}`"
            class="grid transition-[grid-template-rows] duration-300 ease-out"
            :class="faq.open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
          >
            <div class="overflow-hidden">
              <p class="px-5 pb-6 pl-[4.25rem] text-sm leading-relaxed text-slate-400 sm:px-7 sm:pl-[4.5rem]">
                {{ faq.answer }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Credit ──────────────────────────────────────────────────────────── -->
    <footer class="border-t border-white/[0.06]">
      <div
        class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-10 sm:flex-row sm:items-center sm:justify-between lg:px-8"
      >
        <div class="flex items-center gap-4">
          <div class="flex -space-x-2.5">
            <div
              class="grid h-10 w-10 place-items-center rounded-full border-2 border-[#020617] bg-gradient-to-br from-cyan-400 to-blue-600 text-[11px] font-bold text-white"
            >
              NC
            </div>
            <div
              class="grid h-10 w-10 place-items-center rounded-full border-2 border-[#020617] bg-gradient-to-br from-violet-400 to-pink-600 text-[11px] font-bold text-white"
            >
              ST
            </div>
          </div>
          <div class="border-l border-white/10 pl-4">
            <p class="text-sm font-semibold text-white">Developed by Researchers</p>
            <p class="mt-0.5 text-xs text-slate-500">Namoc · Isanan · Prado · Pellazar · Ermita</p>
          </div>
        </div>

        <span class="w-fit rounded-lg border border-white/[0.08] bg-white/[0.03] px-3 py-1.5 text-xs font-medium text-slate-400">
          ⚡ v1.0.0
        </span>
      </div>
    </footer>

    <!-- ── Tutorial dialog ─────────────────────────────────────────────────── -->
    <Teleport to="body">
      <div
        v-if="showTutorial"
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md sm:p-6"
      >
        <div
          ref="tutorialPanel"
          role="dialog"
          aria-modal="true"
          tabindex="-1"
          :aria-label="`Tutorial, step ${currentTutorialStep + 1} of ${activeTutorialData.length}`"
          class="relative flex h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-white/10 bg-[#0B1220] shadow-2xl focus:outline-none"
        >
          <button
            type="button"
            @click="closeTutorial"
            class="absolute right-4 top-4 z-10 rounded-lg border border-white/10 bg-[#0B1220]/80 p-2 text-slate-400 backdrop-blur transition-colors hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            aria-label="Close tutorial"
          >
            <X class="h-4 w-4" />
          </button>

          <div class="h-1 w-full shrink-0 bg-white/[0.06]">
            <div
              class="h-full bg-gradient-to-r from-cyan-400 to-violet-400 transition-[width] duration-500 ease-out"
              :style="{ width: `${((currentTutorialStep + 1) / activeTutorialData.length) * 100}%` }"
            ></div>
          </div>

          <div class="flex flex-1 flex-col items-center overflow-y-auto px-6 py-10 text-center sm:px-10">
            <p
              class="inline-flex shrink-0 items-center rounded-full border border-white/10 bg-white/[0.04] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em] text-cyan-300"
            >
              Step {{ currentTutorialStep + 1 }} of {{ activeTutorialData.length }}
            </p>

            <h3 class="mt-6 max-w-2xl text-xl font-bold leading-snug tracking-tight text-white sm:text-2xl lg:text-3xl">
              {{ activeTutorialData[currentTutorialStep]?.text }}
            </h3>

            <div
              class="group/img relative mt-8 flex w-full max-w-3xl flex-1 cursor-zoom-in items-center justify-center"
              @click="isFullscreen = true"
            >
              <img
                :key="currentTutorialStep"
                :src="activeTutorialData[currentTutorialStep]?.image"
                :alt="'Step ' + (currentTutorialStep + 1)"
                class="max-h-[52vh] w-full rounded-xl border border-white/10 bg-white/[0.03] object-contain shadow-xl"
              />
              <div
                class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-200 group-hover/img:opacity-100"
              >
                <span class="rounded-full bg-black/70 p-3 text-white backdrop-blur-sm">
                  <ZoomIn class="h-5 w-5" />
                </span>
              </div>
            </div>
          </div>

          <div
            class="flex shrink-0 items-center justify-between gap-4 border-t border-white/[0.06] bg-[#020617]/60 px-5 py-4 sm:px-6"
          >
            <button
              type="button"
              @click="prevTutorialStep"
              :disabled="currentTutorialStep === 0"
              class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/[0.03] px-4 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-white/[0.07] hover:text-white disabled:cursor-not-allowed disabled:opacity-40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            >
              <ChevronLeft class="h-4 w-4" />
              <span class="hidden sm:inline">Previous</span>
            </button>

            <div class="hidden items-center gap-2 md:flex">
              <button
                v-for="(_, index) in activeTutorialData"
                :key="index"
                type="button"
                @click="currentTutorialStep = index"
                :aria-label="`Go to step ${index + 1}`"
                :aria-current="currentTutorialStep === index"
                class="h-1.5 rounded-full transition-all duration-300"
                :class="currentTutorialStep === index ? 'w-7 bg-cyan-400' : 'w-1.5 bg-white/20 hover:bg-white/40'"
              ></button>
            </div>

            <button
              v-if="currentTutorialStep < activeTutorialData.length - 1"
              type="button"
              @click="nextTutorialStep"
              class="inline-flex items-center gap-1.5 rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition-colors hover:bg-cyan-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            >
              <span class="hidden sm:inline">Next Step</span>
              <span class="sm:hidden">Next</span>
              <ChevronRight class="h-4 w-4" />
            </button>
            <button
              v-else
              type="button"
              @click="closeTutorial"
              class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-cyan-400 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition-opacity hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-300"
            >
              Got It, Let's Go!
              <Check class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Fullscreen step image -->
      <div
        v-if="isFullscreen"
        class="fixed inset-0 z-[11000] flex items-center justify-center bg-[#020617]/95 p-4 backdrop-blur-xl sm:p-8"
        @click="isFullscreen = false"
      >
        <button
          type="button"
          @click.stop="isFullscreen = false"
          class="absolute right-5 top-5 rounded-lg bg-white/10 p-2.5 text-slate-300 transition-colors hover:bg-white/20 hover:text-white"
          aria-label="Close image"
        >
          <X class="h-6 w-6" />
        </button>
        <img
          :src="activeTutorialData[currentTutorialStep]?.image"
          :alt="'Fullscreen Step ' + (currentTutorialStep + 1)"
          class="h-full w-full cursor-zoom-out select-none object-contain"
          @click.stop="isFullscreen = false"
        />
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { h, ref, nextTick, onMounted, onUnmounted, watch } from 'vue';
import { X, ChevronLeft, ChevronRight, Check, ZoomIn, PlayCircle } from 'lucide-vue-next';

// The top bar and its anchor list are gone. The brand name still appears in the hero
// eyebrow, and "Modules" and "System FAQ" are still section headings, so nothing was
// only reachable through the nav -- but the section ids stay, because they are still
// valid deep-link targets now that nothing in the markup links to them.

// 4-Grid Modules
const businessModules = [
  { tag: 'SCM Module', title: 'Supply Chain', desc: 'End-to-end tracking from supplier to service provider.', action: 'Configure', img: '/scm-meeting.jpg', hoverBorder: 'hover:border-blue-500/30 hover:shadow-[0_20px_40px_-15px_rgba(59,130,246,0.2)]', textColor: 'text-blue-400', dotColor: 'bg-blue-500', glowBg: 'bg-blue-600' },
  { tag: 'CRM + Chat', title: 'Client Engagement', desc: 'Customer profiles, service history & real-time messaging.', action: 'Connect', img: '/crm-engagement.jpg', hoverBorder: 'hover:border-purple-500/30 hover:shadow-[0_20px_40px_-15px_rgba(168,85,247,0.2)]', textColor: 'text-purple-400', dotColor: 'bg-purple-500', glowBg: 'bg-purple-600' },
  { tag: 'Logistics', title: 'Delivery Management', desc: 'Manage delivery schedules, transportation, and order fulfillment.', action: 'Track', img: '/logistics-truck.jpg', hoverBorder: 'hover:border-orange-500/30 hover:shadow-[0_20px_40px_-15px_rgba(249,115,22,0.2)]', textColor: 'text-orange-400', dotColor: 'bg-orange-500', glowBg: 'bg-orange-600' },
  { tag: 'Procurement', title: 'Material Requirements', desc: 'Identify needed products and send requests to suppliers.', action: 'Order', img: '/procurement-warehouse.jpg', hoverBorder: 'hover:border-indigo-500/30 hover:shadow-[0_20px_40px_-15px_rgba(99,102,241,0.2)]', textColor: 'text-indigo-400', dotColor: 'bg-indigo-500', glowBg: 'bg-indigo-600' }
];

// 3-Grid Modules
const extraModules = [
  { tag: 'EM Module', title: 'Employee Management', desc: 'Employee records, roles, and performance monitoring for distributors.', action: 'Manage', img: '/hr-team.jpg', hoverBorder: 'hover:border-amber-500/30 hover:shadow-[0_20px_40px_-15px_rgba(245,158,11,0.2)]', textColor: 'text-amber-400', glowBg: 'bg-amber-600' },
  { tag: 'Finance Module', title: 'Financial Management', desc: 'Track sales, expenses, revenue and profitability analytics.', action: 'View', img: '/finance-calculator.jpg', hoverBorder: 'hover:border-green-500/30 hover:shadow-[0_20px_40px_-15px_rgba(34,197,94,0.2)]', textColor: 'text-green-400', glowBg: 'bg-green-600' },
  { tag: 'E-Commerce', title: 'Online Store', desc: 'Browse products, check availability, and place orders online.', action: 'Shop', img: '/ecommerce-store.jpg', hoverBorder: 'hover:border-pink-500/30 hover:shadow-[0_20px_40px_-15px_rgba(236,72,153,0.2)]', textColor: 'text-pink-400', glowBg: 'bg-pink-600' }
];

// FAQs Array
const faqs = ref([
  {
    question: 'General Information: What is this platform?',
    answer: 'This is a comprehensive Integrated Management System designed specifically for paint distributors and service providers to handle inventory, logistics, and partner management under one umbrella.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    open: false
  },
  {
    question: 'How do I book Appointments for a service provider?',
    answer: 'To book a service appointment, please navigate to the E-Commerce portal and select the "Services" option from the top navigation bar. From there, you may browse and select the specific service professional you require.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    open: false
  },
  {
    question: 'How does Products & Shopping work?',
    answer: 'To purchase products, navigate to the E-Commerce portal and select the "Shop" tab located on the top navigation bar. You may then browse our comprehensive catalog for the items you need or desire.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />',
    open: false
  },
  {
    question: 'What is Virtual Paint Color Mixing?',
    answer: 'The VR Color Mixing tool provides an interactive environment where users can simulate blending different paint shades in real-time, allowing you to preview custom colors before making a purchase.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />',
    open: false
  },
  {
    question: 'What does the Decision Support System (DSS) do?',
    answer: 'The DSS leverages historical sales and geographic data to provide analytics, demand forecasting, and priority rankings to help administrators and distributors make well-informed business decisions.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
    open: false
  },
  {
    question: 'How do I register as a user (Client, Service Provider, Distributor, and Supplier)?',
    answer: 'Prospective users can initiate the registration process by navigating to the designated sign-up portal. You will be prompted to select your intended account type—Client, Service Provider, Distributor, or Supplier—and submit the appropriate credentials. Upon successful review and verification, your specific portal access will be granted.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />',
    open: false
  },
  {
    question: 'Account & Security: What if I hit the maximum account submissions or face errors?',
    answer: 'If you encounter continuous account errors, trigger brute-force warnings, or reach the maximum account submission limit, your session may be flagged for security. Please contact the system administrator immediately to restore access or resolve the issue.',
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />',
    open: false
  }
]);

const toggleFaq = (index) => {
  faqs.value.forEach((faq, i) => {
    if (i !== index) faq.open = false;
  });
  faqs.value[index].open = !faqs.value[index].open;
};

// NEW: Global Tutorial State and Handlers
const showTutorial = ref(false);
const currentTutorialStep = ref(0);
const isFullscreen = ref(false);
const activeTutorialData = ref([]);
const tutorialPanel = ref(null);

const clientTutorialSteps = [
  { image: '/ClientTutorial/001.png', text: 'Select a service that aligns with your specific requirements and needs.' },
  { image: '/ClientTutorial/002.png', text: 'Complete the service request form by providing all necessary information.' },
  { image: '/ClientTutorial/003.png', text: 'Await official confirmation from the selected service provider.' },
  { image: '/ClientTutorial/004.png', text: 'Review and sign the survey agreement to authorize the service provider to inspect the requested area.' },
  { image: '/ClientTutorial/005.png', text: 'Upon completion of the survey, navigate to your messages to proceed with the service fulfillment process.' },
  { image: '/ClientTutorial/006.png', text: 'Carefully review the formal request details sent by the service provider to ensure they match your original requirements.' },
  { image: '/ClientTutorial/007.png', text: 'Accept or negotiate the official service terms with the provider until an agreement is reached. (Note: Fixed-price services are non-negotiable.)' },
  { image: '/ClientTutorial/008.png', text: 'Finalize the payment method and terms by accepting or negotiating the conditions set by the service provider.' },
  { image: '/ClientTutorial/009.png', text: 'Return to the Service Request dashboard to monitor the progress and completion of the agreed-upon work.' },
  { image: '/ClientTutorial/010.png', text: 'Once the service provider submits a completion request, review the work. You may approve it to finalize the job, or decline and request revisions if any requirements were not met.' },
  { image: '/ClientTutorial/011.png', text: 'Ensure timely compliance with the payment terms. (Note: The payment process is dictated by the conditions agreed upon during the negotiation phase.)' }
];

const spTutorialSteps = [
  { image: '/SPTutorial/001.png', text: 'Formulate a comprehensive service offering that accurately represents your professional capabilities.' },
  { image: '/SPTutorial/002.png', text: 'Complete the service listing form by providing all required and relevant information.' },
  { image: '/SPTutorial/003.png', text: 'Await service requests or bookings from prospective clients.' },
  { image: '/SPTutorial/004.png', text: 'Generate a formal survey agreement to establish legal authorization for inspecting the client\'s premises.' },
  { image: '/SPTutorial/005.png', text: 'Affix your digital signature to validate the survey agreement.' },
  { image: '/SPTutorial/006.png', text: 'Await the client\'s countersignature and approval of the survey agreement.' },
  { image: '/SPTutorial/007.png', text: 'Upon client approval, initiate the official survey by clicking "Start Survey" in the system before conducting the physical inspection, and ensure you click "End Survey" upon completion.' },
  { image: '/SPTutorial/008.png', text: 'Evaluate the survey results and determine whether to accept or decline the requested service.' },
  { image: '/SPTutorial/009.png', text: 'Utilize the integrated messaging system to coordinate further details regarding the service fulfillment.' },
  { image: '/SPTutorial/010.png', text: 'Transmit the finalized request details to the client for mutual understanding.' },
  { image: '/SPTutorial/011.png', text: 'Draft an Official Deal and engage in negotiations until a mutually agreeable price is established. (Note: Fixed-Price services are non-negotiable).' },
  { image: '/SPTutorial/012.png', text: 'Select the appropriate payment method and clearly define the payment conditions.' },
  { image: '/SPTutorial/013.png', text: 'Navigate to the Service Jobs dashboard to monitor the progress and oversee the fulfillment of the agreed-upon work.' },
  { image: '/SPTutorial/014.png', text: 'Submit official proof of completion once the service is rendered. Note: Any incomplete tasks may result in the client declining the completion request, requiring you to fulfill the remaining obligations.' },
  { image: '/SPTutorial/015.png', text: 'If the service is completed but payment is pending, you may issue an email reminder. Should the maximum reminder attempts be reached, a formal document will be generated to assist you in pursuing legal recourse.' }
];

const openTutorial = (type) => {
  activeTutorialData.value = type === 'client' ? clientTutorialSteps : spTutorialSteps;
  currentTutorialStep.value = 0;
  isFullscreen.value = false;
  showTutorial.value = true;
};

const closeTutorial = () => {
  showTutorial.value = false;
  isFullscreen.value = false;
  setTimeout(() => {
    currentTutorialStep.value = 0;
    activeTutorialData.value = [];
  }, 300);
};

const nextTutorialStep = () => {
  if (currentTutorialStep.value < activeTutorialData.value.length - 1) {
    currentTutorialStep.value++;
  }
};

const prevTutorialStep = () => {
  if (currentTutorialStep.value > 0) {
    currentTutorialStep.value--;
  }
};

/*
 * Escape closes whichever layer is on top, and the page behind an open dialog does
 * not scroll. Both were missing: the overlay had no key handler at all, and a dialog
 * that lets the page scroll behind it drags the content it is meant to be covering.
 */
const handleKeydown = (e) => {
  if (e.key !== 'Escape') return;

  if (isFullscreen.value) {
    isFullscreen.value = false;
    return;
  }

  if (showTutorial.value) closeTutorial();
};

watch([showTutorial, isFullscreen], ([tutorial, fullscreen]) => {
  const locked = tutorial || fullscreen;

  if (typeof document === 'undefined') return;

  document.body.style.overflow = locked ? 'hidden' : '';
});

// Moves focus into the dialog on open so a keyboard user is not left behind on the
// page underneath it, and hands it back on close.
watch(showTutorial, async (open) => {
  if (!open) return;

  await nextTick();
  tutorialPanel.value?.focus?.();
});

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

/*
 * Scroll handling for the hero is no longer needed.
 *
 * The WebGL scene used to sit over a full-height hero and swallow wheel events,
 * which trapped anyone on that section with no way to reach the rest of the page --
 * so a global wheel listener forwarded the delta to the window, and guarded the
 * target because a wheel over bare text or the document has no `closest`. With the
 * scene gone, none of that has anything to do, so all of it is gone with it.
 */
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);

  // The tutorial overlay sets this, so leaving it behind would leave the page
  // unscrollable after navigating away with the dialog open.
  document.body.style.overflow = '';
});

// Minimal Icons Setup
const InventoryIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })]); }};
const DSSIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })]); }};
const VirtualMixIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01' })]); }};
const MobileIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' })]); }};
const HRIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })]); }};
const FinanceIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })]); }};
const SCMIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' })]); }};
const CRMIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5' })]); }};
const ChatIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' })]); }};
const EcommerceIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' })]); }};
const ProcurementIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' })]); }};
const LogisticsIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM9 17h10M5 17h2m3-4h6m-9-4h6m-6 4h2M5 9h14M3 3l2 2m0 0l-2 2m2-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z' })]); }};

const systemFeatures = [
  { name: 'Inventory Mgmt', icon: InventoryIcon },
  { name: 'Decision System', icon: DSSIcon },
  { name: 'Virtual Mixing', icon: VirtualMixIcon },
  { name: 'Mobile Platform', icon: MobileIcon },
  { name: 'Employee Mgmt', icon: HRIcon },
  { name: 'Finance Hub', icon: FinanceIcon },
  { name: 'Supply Chain', icon: SCMIcon },
  { name: 'Client Relations', icon: CRMIcon },
  { name: 'Live Chat', icon: ChatIcon },
  { name: 'E-Commerce', icon: EcommerceIcon },
  { name: 'Procurement', icon: ProcurementIcon },
  { name: 'Logistics', icon: LogisticsIcon }
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

/*
 * A single faint grid behind the whole page. It gives the flat background some
 * structure without the per-section orb stacking, and it is masked so it fades out
 * rather than tiling edge to edge.
 */
.landing-grid {
  background-image:
    linear-gradient(to right, rgba(148, 163, 184, 0.055) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(148, 163, 184, 0.055) 1px, transparent 1px);
  background-size: 56px 56px;
  mask-image: radial-gradient(ellipse 100% 60% at 50% 0%, #000 30%, transparent 75%);
  -webkit-mask-image: radial-gradient(ellipse 100% 60% at 50% 0%, #000 30%, transparent 75%);
}

.landing-backdrop {
  /* The blur layers are expensive on a fixed element, so they are promoted once. */
  will-change: transform;
}

/*
 * The hero's 3D stack, in CSS rather than in a canvas.
 *
 * `perspective` on the stage and `transform-style: preserve-3d` on the tilt are both
 * required: perspective alone projects the element it sits on, and each child would
 * still flatten onto its own plane. Preserving 3D is what lets the back plate and the
 * front pane sit at genuine distances from the vanishing point instead of just being
 * offset rectangles.
 *
 * The Y rotation is what produces the visible side edge, so it is deliberately modest
 * -- pushed further, the photograph distorts enough to look broken rather than deep.
 */
.hero-stage {
  perspective: 1400px;
}

.hero-tilt {
  transform-style: preserve-3d;
  transform: rotateY(-13deg) rotateX(5deg);
  transition: transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
}

/*
 * Pointer-driven parallax: the whole stack leans a few degrees toward the cursor.
 * Scoped to devices with a real hover, because on touch the first tap fires it and
 * the tilt sticks at whatever angle it last saw -- a stuck, arbitrarily rotated hero
 * is worse than no parallax at all.
 */
@media (hover: hover) and (pointer: fine) {
  .hero-stage:hover .hero-tilt {
    transform: rotateY(-6deg) rotateX(2deg);
  }
}

/*
 * A slow continuous drift, independent of the hover rule above. The two would fight
 * over `transform` if the drift animated the same element the hover did, so it lives
 * on the photograph instead and the hover still moves the outer stack.
 */
.hero-plate {
  animation: hero-drift 14s ease-in-out infinite;
}

@keyframes hero-drift {
  0%,
  100% {
    transform: translateZ(0);
  }
  50% {
    transform: translateZ(28px);
  }
}

.hero-plate-back {
  transform: translateZ(-70px);
}

.hero-pane-front {
  transform: translateZ(85px);
}

.hero-pane-back {
  transform: translateZ(-35px);
}

/*
 * Every animation on the page is decorative, so all of them stand down together for
 * anyone who has asked their system to reduce motion. Without this the pulsing status
 * dot, the ping, the tutorial progress slide and the hero drift all keep running.
 */
@media (prefers-reduced-motion: reduce) {
  .landing-backdrop *,
  .landing-grid,
  .hero-plate {
    animation: none !important;
    transition-duration: 0.01ms !important;
  }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 10px;
}
::-webkit-scrollbar-track {
  background: #020617;
}
::-webkit-scrollbar-thumb {
  background: #1e293b;
  border-radius: 999px;
  border: 2px solid #020617;
}
::-webkit-scrollbar-thumb:hover {
  background: #38bdf8;
}

/* Keyframes for New Animations */
@keyframes gradient-x {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
.animate-gradient {
  animation: gradient-x 6s ease infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0) translateX(-50%); }
  50% { transform: translateY(-10px) translateX(-50%); }
}
.animate-float {
  animation: float 3s ease-in-out infinite;
}

@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin-slow {
  animation: spin-slow 8s linear infinite;
}
</style>
