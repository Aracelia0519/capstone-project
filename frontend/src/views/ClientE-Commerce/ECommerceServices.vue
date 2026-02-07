<template>
  <div class="min-h-screen bg-gradient-to-b">
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900">Professional Services</h1>
        <p class="text-gray-600 mt-2">Expert painting and consultation services for your home or business</p>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <Card class="rounded-2xl shadow-lg border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-blue-50 to-indigo-50 relative">
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-24 h-24 text-blue-400 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div class="absolute top-4 left-4">
              <Badge class="bg-blue-600 hover:bg-blue-700 text-white border-0">Most Popular</Badge>
            </div>
          </div>
          
          <CardContent class="p-6">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold text-gray-900">Interior Painting</h3>
                <p class="text-sm text-gray-500 mt-1">Walls, ceilings, trim work</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">From ₱5,000</div>
                <div class="text-sm text-gray-500">per room</div>
              </div>
            </div>

            <div class="mt-4 space-y-3">
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Professional prep work</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Cleanup included</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">2-year warranty</span>
              </div>
            </div>
          </CardContent>

          <CardFooter class="p-6 pt-0 border-t border-gray-100 mt-6 flex justify-between items-center">
             <div class="w-full pt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                  <span class="font-medium text-gray-900">3-5 days</span> completion
                </div>
                <Button
                  @click="openServiceModal('interior')"
                  class="bg-blue-600 hover:bg-blue-700 text-white"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  <span>Request Service</span>
                </Button>
             </div>
          </CardFooter>
        </Card>

        <Card class="rounded-2xl shadow-lg border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-orange-50 to-red-50 relative">
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-24 h-24 text-orange-400 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"></path>
              </svg>
            </div>
          </div>
          
          <CardContent class="p-6">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold text-gray-900">Exterior Painting</h3>
                <p class="text-sm text-gray-500 mt-1">House exterior, fences, gates</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">From ₱15,000</div>
                <div class="text-sm text-gray-500">per project</div>
              </div>
            </div>

            <div class="mt-4 space-y-3">
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Weather-resistant paint</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Pressure washing included</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">5-year warranty</span>
              </div>
            </div>
          </CardContent>

          <CardFooter class="p-6 pt-0 border-t border-gray-100 mt-6">
            <div class="w-full pt-6 flex justify-between items-center">
              <div class="text-sm text-gray-500">
                <span class="font-medium text-gray-900">5-10 days</span> completion
              </div>
              <Button
                @click="openServiceModal('exterior')"
                class="bg-blue-600 hover:bg-blue-700 text-white"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Request Service</span>
              </Button>
            </div>
          </CardFooter>
        </Card>

        <Card class="rounded-2xl shadow-lg border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-purple-50 to-pink-50 relative">
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-24 h-24 text-purple-400 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
              </svg>
            </div>
          </div>
          
          <CardContent class="p-6">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold text-gray-900">Color Consultation</h3>
                <p class="text-sm text-gray-500 mt-1">Professional color advice</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">From ₱2,500</div>
                <div class="text-sm text-gray-500">per session</div>
              </div>
            </div>

            <div class="mt-4 space-y-3">
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Personalized color scheme</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Digital mockups</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Lighting analysis</span>
              </div>
            </div>
          </CardContent>

          <CardFooter class="p-6 pt-0 border-t border-gray-100 mt-6">
            <div class="w-full pt-6 flex justify-between items-center">
              <div class="text-sm text-gray-500">
                <span class="font-medium text-gray-900">1-2 hours</span> session
              </div>
              <Button
                @click="openServiceModal('consultation')"
                class="bg-blue-600 hover:bg-blue-700 text-white"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Request Service</span>
              </Button>
            </div>
          </CardFooter>
        </Card>

        <Card class="rounded-2xl shadow-lg border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-green-50 to-emerald-50 relative">
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-24 h-24 text-green-400 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
          </div>
          
          <CardContent class="p-6">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold text-gray-900">Maintenance</h3>
                <p class="text-sm text-gray-500 mt-1">Touch-ups, repairs, cleaning</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">From ₱3,000</div>
                <div class="text-sm text-gray-500">per visit</div>
              </div>
            </div>

            <div class="mt-4 space-y-3">
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Minor paint repairs</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Wall cleaning</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Inspection report</span>
              </div>
            </div>
          </CardContent>

          <CardFooter class="p-6 pt-0 border-t border-gray-100 mt-6">
            <div class="w-full pt-6 flex justify-between items-center">
              <div class="text-sm text-gray-500">
                <span class="font-medium text-gray-900">2-4 hours</span> per visit
              </div>
              <Button
                @click="openServiceModal('maintenance')"
                class="bg-blue-600 hover:bg-blue-700 text-white"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Request Service</span>
              </Button>
            </div>
          </CardFooter>
        </Card>

        <Card class="rounded-2xl shadow-lg border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
          <div class="h-48 bg-gradient-to-br from-gray-50 to-slate-50 relative">
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-24 h-24 text-gray-400 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
          </div>
          
          <CardContent class="p-6">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-xl font-bold text-gray-900">Commercial Painting</h3>
                <p class="text-sm text-gray-500 mt-1">Offices, shops, warehouses</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">Custom Quote</div>
                <div class="text-sm text-gray-500">Contact for pricing</div>
              </div>
            </div>

            <div class="mt-4 space-y-3">
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">After-hours work available</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Industrial-grade materials</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm">Project management</span>
              </div>
            </div>
          </CardContent>

          <CardFooter class="p-6 pt-0 border-t border-gray-100 mt-6">
            <div class="w-full pt-6 flex justify-between items-center">
              <div class="text-sm text-gray-500">
                <span class="font-medium text-gray-900">Varies</span> by project
              </div>
              <Button
                @click="openServiceModal('commercial')"
                class="bg-blue-600 hover:bg-blue-700 text-white"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Request Quote</span>
              </Button>
            </div>
          </CardFooter>
        </Card>

        <Card class="rounded-2xl shadow-lg border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 border-dashed border-2">
          <div class="h-48 bg-gradient-to-br from-yellow-50 to-amber-50 relative">
            <div class="absolute inset-0 flex items-center justify-center">
              <svg class="w-24 h-24 text-yellow-400 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
            </div>
          </div>
          
          <CardContent class="p-6">
            <div class="text-center">
              <h3 class="text-xl font-bold text-gray-900 mb-2">Custom Service</h3>
              <p class="text-gray-600 mb-6">Need something specific? We can customize a service package for you.</p>
              
              <Button
                @click="openServiceModal('custom')"
                class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white mx-auto"
                size="lg"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span>Contact Us</span>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card class="mt-16 bg-white rounded-2xl shadow-lg border-gray-200">
        <CardContent class="p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">How Our Services Work</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
              <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
              </div>
              <h3 class="font-semibold text-gray-900 mb-2">1. Request Service</h3>
              <p class="text-gray-600">Choose a service and provide project details</p>
            </div>
            <div class="text-center">
              <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <h3 class="font-semibold text-gray-900 mb-2">2. Get Quote</h3>
              <p class="text-gray-600">Receive detailed estimate and timeline</p>
            </div>
            <div class="text-center">
              <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <h3 class="font-semibold text-gray-900 mb-2">3. Service Delivery</h3>
              <p class="text-gray-600">Professional team executes the project</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Dialog :open="showServiceModal" @update:open="(val) => !val && closeServiceModal()">
      <DialogContent class="max-w-md max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold text-gray-900">Request {{ selectedService?.name }} Service</DialogTitle>
        </DialogHeader>

        <div class="bg-blue-50 rounded-lg p-4 mb-2">
          <div class="flex justify-between items-center">
            <div>
              <h4 class="font-semibold text-gray-900">{{ selectedService?.name }}</h4>
              <p class="text-sm text-gray-600">{{ selectedService?.description }}</p>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold text-gray-900">{{ selectedService?.price }}</div>
              <div class="text-sm text-gray-500">{{ selectedService?.duration }}</div>
            </div>
          </div>
        </div>

        <form @submit.prevent="submitServiceRequest" class="space-y-4">
          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Project Description</Label>
            <Textarea
              v-model="serviceRequest.description"
              rows="3"
              placeholder="Describe your project (room size, surfaces, etc.)"
              required
            />
          </div>

          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Preferred Date</Label>
            <Input
              v-model="serviceRequest.preferredDate"
              type="date"
              required
            />
          </div>

          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Time Preference</Label>
            <Select v-model="serviceRequest.timePreference">
              <SelectTrigger>
                <SelectValue placeholder="Select time preference" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="morning">Morning (8AM-12PM)</SelectItem>
                <SelectItem value="afternoon">Afternoon (1PM-5PM)</SelectItem>
                <SelectItem value="evening">Evening (6PM-9PM)</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</Label>
            <Input
              v-model="serviceRequest.contact"
              type="tel"
              placeholder="+63 912 345 6789"
              required
            />
          </div>

          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Address</Label>
            <Textarea
              v-model="serviceRequest.address"
              rows="2"
              placeholder="Service location address"
              required
            />
          </div>

          <div class="pt-4 border-t border-gray-200">
            <div class="flex justify-between mb-4">
              <span class="text-gray-700">Estimated Cost</span>
              <span class="font-semibold text-gray-900">{{ selectedService?.price }}</span>
            </div>
            
            <Button
              type="submit"
              class="w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <span>Submit Service Request</span>
            </Button>
            
            <p class="text-xs text-gray-500 text-center mt-3">
              A representative will contact you within 24 hours to confirm details
            </p>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue'
// shadcn components
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const services = ref([
  {
    id: 'interior',
    name: 'Interior Painting',
    description: 'Walls, ceilings, trim work',
    price: 'From ₱5,000',
    duration: '3-5 days completion'
  },
  {
    id: 'exterior',
    name: 'Exterior Painting',
    description: 'House exterior, fences, gates',
    price: 'From ₱15,000',
    duration: '5-10 days completion'
  },
  {
    id: 'consultation',
    name: 'Color Consultation',
    description: 'Professional color advice',
    price: 'From ₱2,500',
    duration: '1-2 hours session'
  },
  {
    id: 'maintenance',
    name: 'Maintenance',
    description: 'Touch-ups, repairs, cleaning',
    price: 'From ₱3,000',
    duration: '2-4 hours per visit'
  },
  {
    id: 'commercial',
    name: 'Commercial Painting',
    description: 'Offices, shops, warehouses',
    price: 'Custom Quote',
    duration: 'Varies by project'
  },
  {
    id: 'custom',
    name: 'Custom Service',
    description: 'Tailored to your needs',
    price: 'Contact for quote',
    duration: 'To be determined'
  }
])

const showServiceModal = ref(false)
const selectedService = ref(null)

const serviceRequest = ref({
  description: '',
  preferredDate: '',
  timePreference: '',
  contact: '',
  address: ''
})

const openServiceModal = (serviceId) => {
  selectedService.value = services.value.find(s => s.id === serviceId) || services.value[5]
  showServiceModal.value = true
}

const closeServiceModal = () => {
  showServiceModal.value = false
  selectedService.value = null
  // Reset form
  serviceRequest.value = {
    description: '',
    preferredDate: '',
    timePreference: '',
    contact: '',
    address: ''
  }
}

const submitServiceRequest = () => {
  console.log('Service request submitted:', {
    service: selectedService.value,
    request: serviceRequest.value
  })
  alert('Service request submitted successfully! A representative will contact you within 24 hours.')
  closeServiceModal()
}
</script>