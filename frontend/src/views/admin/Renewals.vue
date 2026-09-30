<template>
  <div class="min-h-screen p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Document Renewals</h1>
        <p class="text-slate-500 mt-1">
          Suppliers and distributors whose DTI Certificate or Mayor's Permit expires within
          {{ windowDays }} days.
        </p>
      </div>
      <Button variant="outline" :disabled="loading" class="gap-2" @click="fetchRenewals">
        <svg
          class="w-4 h-4"
          :class="{ 'animate-spin': loading }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          />
        </svg>
        Refresh
      </Button>
    </div>

    <!-- Filters -->
    <Card class="border-slate-200 shadow-sm">
      <div class="p-4 flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1">
          <Label class="text-slate-500 text-xs mb-1 block">Search</Label>
          <input
            v-model="search"
            type="search"
            placeholder="Company, name or email"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            @input="page = 1"
          />
        </div>
        <div class="w-full sm:w-52">
          <Label class="text-slate-500 text-xs mb-1 block">Role</Label>
          <select
            v-model="role"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            @change="page = 1"
          >
            <option value="all">All roles</option>
            <option value="distributor">Distributor</option>
            <option value="supplier">Supplier</option>
          </select>
        </div>
        <div class="w-full sm:w-48">
          <Label class="text-slate-500 text-xs mb-1 block">Window</Label>
          <select
            v-model.number="windowDays"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            @change="page = 1"
          >
            <option :value="30">30 days</option>
            <option :value="60">60 days</option>
            <option :value="90">90 days</option>
          </select>
        </div>
        <!--
          Opt-in, because renewing is what pushes an account out of the window:
          once the new permit is on file, the row that the submission came from
          disappears from the expiry table. Without this, a submitted renewal
          would be invisible until its next expiry -- the worst possible moment to
          be waiting on a decision.
        -->
        <div class="w-full sm:w-56">
          <Label class="text-slate-500 text-xs mb-1 block">Review queue</Label>
          <button
            type="button"
            @click="toggleReviewFilter"
            class="w-full flex items-center justify-between gap-2 rounded-lg border px-3 py-2 text-sm font-medium"
            :class="reviewFilter === 'pending'
              ? 'border-amber-400 bg-amber-50 text-amber-800'
              : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
          >
            <span>
              {{ reviewFilter === 'pending' ? 'Awaiting my review' : 'Expiring only' }}
            </span>
            <span
              v-if="meta.pending_review_total"
              class="rounded-full px-2 py-0.5 text-[11px] font-bold"
              :class="meta.pending_review_total > 0 ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-600'"
            >
              {{ meta.pending_review_total }}
            </span>
          </button>
        </div>
      </div>

      <p
        v-if="reviewFilter === 'pending' && meta.pending_review_total"
        class="mt-3 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2"
      >
        Showing accounts with a renewal waiting on a decision, alongside the ones
        nearing expiration. A renewed account is listed here even though its new
        documents are not expiring yet.
      </p>
    </Card>

    <!-- Table -->
    <Card class="border-slate-200 shadow-sm overflow-hidden flex flex-col">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-slate-50 border-b border-slate-100">
            <TableRow>
              <TableHead class="font-semibold text-slate-600">Account</TableHead>
              <TableHead class="font-semibold text-slate-600">Role</TableHead>
              <TableHead class="font-semibold text-slate-600">Nearing Expiration</TableHead>
              <TableHead class="font-semibold text-slate-600">Expires</TableHead>
              <TableHead class="font-semibold text-slate-600">Status</TableHead>
              <TableHead class="font-semibold text-slate-600 text-right">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="6" class="h-32 text-center text-slate-500">
                <div class="flex justify-center items-center gap-2">
                  <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                  Loading renewals...
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-else-if="filteredRows.length === 0">
              <TableCell colspan="6" class="h-32 text-center text-slate-500">
                <template v-if="search.trim()">
                  No account matches "{{ search.trim() }}".
                </template>
                <template v-else-if="reviewFilter === 'pending'">
                  No renewals are waiting on a decision, and no documents are nearing expiration.
                </template>
                <template v-else>
                  No documents are nearing expiration.
                </template>
              </TableCell>
            </TableRow>

            <TableRow v-for="row in filteredRows" :key="row.user.id" class="hover:bg-slate-50">
              <TableCell>
                <p class="font-semibold text-slate-900">{{ row.user.company_name || '—' }}</p>
                <p class="text-xs text-slate-500">{{ row.user.name || '—' }}</p>
                <p class="text-xs text-slate-400">{{ row.user.email }}</p>
              </TableCell>
              <TableCell>
                <Badge variant="outline" class="capitalize">{{ row.user.role }}</Badge>
              </TableCell>
              <TableCell>
                <div class="flex flex-wrap gap-1">
                  <!--
                    Shown even when nothing is expiring. A row can be here purely
                    because a renewal is waiting on a decision, and an account
                    with a submitted renewal and no upcoming expiry is precisely
                    the one an admin must not skim past.
                  -->
                  <Badge
                    v-if="row.pending_reviews > 0"
                    class="bg-amber-600 text-white gap-1"
                  >
                    Renewal awaiting review
                    <span class="font-normal opacity-90">{{ row.pending_reviews }}</span>
                  </Badge>
                  <Badge
                    v-for="doc in row.expiring"
                    :key="doc.key"
                    :class="stateClass(doc.state)"
                    class="gap-1"
                  >
                    {{ doc.label }}
                    <span class="font-normal opacity-90">{{ countdown(doc.days_remaining) }}</span>
                  </Badge>
                  <span
                    v-if="row.expiring.length === 0"
                    class="text-xs text-slate-400"
                  >
                    Nothing expiring
                  </span>
                </div>
              </TableCell>
              <TableCell>
                <!--
                  row.soonest is null for a review-only row, so this is guarded
                  rather than assumed. The list is no longer strictly
                  "has an expiring document".
                -->
                <template v-if="row.soonest">
                  <p class="text-sm font-semibold" :class="row.is_overdue ? 'text-red-600' : 'text-slate-900'">
                    {{ formatDate(row.soonest.expiration_at) }}
                  </p>
                  <p class="text-xs" :class="row.is_overdue ? 'text-red-500' : 'text-slate-500'">
                    {{ row.soonest.label }}
                  </p>
                </template>
                <span v-else class="text-xs text-slate-400">—</span>
              </TableCell>
              <TableCell>
                <Badge :class="statusClass(row)">{{ statusLabel(row) }}</Badge>
              </TableCell>
              <TableCell class="text-right">
                <Button size="sm" variant="outline" @click="openDetails(row)">View Details</Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-slate-100">
        <p class="text-xs text-slate-500">
          Showing {{ rows.length }} of {{ meta.total }} account(s)
        </p>
        <div class="flex gap-2">
          <Button size="sm" variant="outline" :disabled="page <= 1" @click="page--; fetchRenewals()">
            Previous
          </Button>
          <Button
            size="sm" variant="outline"
            :disabled="page >= meta.last_page"
            @click="page++; fetchRenewals()"
          >
            Next
          </Button>
        </div>
      </div>
    </Card>

    <!-- Details modal -->
    <Dialog :open="showModal" @update:open="showModal = $event">
      <div
        v-if="showModal"
        class="fixed inset-0 z-40 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      >
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
          <!-- Header -->
          <div class="p-6 border-b border-slate-100 flex justify-between items-start bg-slate-50 gap-4">
            <div>
              <h2 class="text-xl font-bold text-slate-900">{{ detail?.user?.company_name || 'Account' }}</h2>
              <p class="text-sm text-slate-500 mt-1">
                {{ detail?.user?.name }} · {{ detail?.user?.email }}
              </p>
              <div class="flex gap-2 mt-2">
                <Badge variant="outline" class="capitalize">{{ detail?.user?.role }}</Badge>
                <Badge :class="detail?.user?.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'">
                  {{ detail?.user?.status }}
                </Badge>
              </div>
            </div>
            <button
              class="text-slate-400 hover:text-slate-600 text-2xl leading-none"
              aria-label="Close"
              @click="showModal = false"
            >
              &times;
            </button>
          </div>

          <div v-if="detailLoading" class="p-10 text-center text-slate-500">Loading account...</div>

          <div v-else-if="detail" class="p-6 overflow-y-auto flex-1 space-y-6">
            <!-- Terminated banner -->
            <div
              v-if="detail.termination"
              class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800"
            >
              <p class="font-bold">Account terminated</p>
              <p class="mt-1">{{ detail.termination.reason }}</p>
              <p class="mt-1 text-xs">
                Terminated {{ formatDateTime(detail.termination.terminated_at) }}
              </p>
            </div>

            <!-- The two tracked documents -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="doc in trackedDocuments"
                :key="doc.key"
                class="rounded-xl border p-4"
                :class="docBorder(doc)"
              >
                <div class="flex items-center justify-between gap-2 mb-2">
                  <h3 class="font-bold text-slate-900">{{ doc.label }}</h3>
                  <Badge :class="stateClass(doc.state)">{{ stateLabel(doc) }}</Badge>
                </div>

                <p
                  class="text-2xl font-bold mb-1"
                  :class="doc.days_remaining < 0 ? 'text-red-600' : 'text-slate-900'"
                >
                  {{ doc.days_remaining === null ? 'No date on record' : countdown(doc.days_remaining) }}
                </p>
                <p class="text-xs text-slate-500 mb-3">
                  Expires {{ formatDate(doc.expiration_at) }}
                </p>

                <!--
                  Per-document warning, for when only one permit is due.
                  The three states below are exactly DocumentExpiry::needsRenewal()
                  (days <= 30): overdue, urgent, or inside the window.
                -->
                <Button
                  v-if="isWarnable(doc)"
                  size="sm"
                  variant="outline"
                  class="w-full mb-3"
                  :disabled="acting"
                  @click="askNotify(doc.key)"
                >
                  Warn about {{ doc.label }}
                </Button>

                <!-- The whole point of the screen: the file next to the date. -->
                <div v-if="doc.file_url" class="space-y-2">
                  <a
                    :href="doc.file_url"
                    target="_blank"
                    rel="noopener"
                    class="text-xs font-semibold text-indigo-600 hover:underline"
                  >
                    Open uploaded document
                  </a>
                  <img
                    v-if="isImage(doc.file_path)"
                    :src="doc.file_url"
                    :alt="doc.label"
                    class="w-full rounded-lg border border-slate-200 object-contain max-h-64"
                  >
                  <div v-else class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
                    PDF document — use the link above to open it.
                  </div>
                </div>
                <div v-else class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                  No file was uploaded for this document, so the date cannot be verified against it.
                </div>
              </div>
            </div>

            <!-- Related documents -->
            <div>
              <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b pb-2 mb-3">
                Related Documents
              </h3>
              <p v-if="detail.related_documents.length === 0" class="text-sm text-slate-500">
                No related documents were submitted.
              </p>
              <div v-else class="space-y-2">
                <div
                  v-for="doc in detail.related_documents"
                  :key="doc.id"
                  class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 p-3"
                >
                  <div class="min-w-0">
                    <p class="font-semibold text-slate-900 text-sm truncate">{{ doc.document_name }}</p>
                    <p class="text-xs text-slate-500">
                      Expires {{ formatDate(doc.expiration_at) }}
                      <span v-if="doc.days_remaining !== null" class="text-slate-400">
                        ({{ countdown(doc.days_remaining) }})
                      </span>
                    </p>
                  </div>
                  <div class="flex items-center gap-2">
                    <Badge :class="relatedStatusClass(doc.status)">{{ doc.status }}</Badge>
                    <a
                      v-if="doc.file_url" :href="doc.file_url" target="_blank" rel="noopener"
                      class="text-xs font-semibold text-indigo-600 hover:underline"
                    >
                      Open
                    </a>
                    <Button
                      v-if="!acting" size="sm" variant="ghost"
                      class="text-emerald-700 hover:bg-emerald-50"
                      @click="askReviewRelated(doc, 'approved')"
                    >
                      Approve
                    </Button>
                    <Button
                      v-if="!acting" size="sm" variant="ghost"
                      class="text-red-700 hover:bg-red-50"
                      @click="askReviewRelated(doc, 'rejected')"
                    >
                      Reject
                    </Button>
                  </div>
                </div>
                <p v-if="relatedRejectionReason" class="text-xs text-red-600">
                  {{ relatedRejectionReason }}
                </p>
              </div>
            </div>

            <!--
              Renewal review. Deliberately above the "Date Verification" block: this
              is the action an admin came here for when a renewal is pending, and
              burying it under a bulk "Confirm Dates Match" button that is refused
              while reviews are open is how a renewal queue gets ignored.
            -->
            <div
              v-if="pendingReviews.length"
              class="rounded-lg border-2 border-amber-300 bg-amber-50/50 p-4"
            >
              <h3 class="font-bold text-amber-900 text-sm">
                Renewal awaiting your decision ({{ pendingReviews.length }})
              </h3>
              <p class="text-xs text-amber-800 mt-1">
                Check each file against the expiration date printed on it before approving.
                The file shown is the exact upload that was submitted, even if a newer one has replaced it since.
              </p>

              <div class="mt-3 space-y-3">
                <div
                  v-for="review in pendingReviews"
                  :key="review.id"
                  class="rounded-lg border border-amber-200 bg-white p-3"
                >
                  <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                      <p class="font-semibold text-slate-900 text-sm">{{ review.label }}</p>
                      <p class="text-xs text-slate-500">
                        Submitted {{ formatDateTime(review.submitted_at) }} &middot;
                        expires {{ formatDate(review.expiration_at) }}
                      </p>
                    </div>
                    <a
                      v-if="review.file_url" :href="review.file_url" target="_blank" rel="noopener"
                      class="text-xs font-semibold text-indigo-600 hover:underline"
                    >
                      Open submitted file
                    </a>
                  </div>

                  <div
                    v-if="rejectingKey === review.document_key"
                    class="mt-3 rounded-md border border-red-200 bg-red-50 p-3"
                  >
                    <label
                      :for="`reject-reason-${review.document_key}`"
                      class="block text-xs font-semibold text-red-800"
                    >
                      Reason for rejection
                    </label>
                    <p class="text-[11px] text-red-700 mt-0.5">
                      Shown to the user. Say what is wrong with the file so they know what to re-upload.
                    </p>
                    <textarea
                      :id="`reject-reason-${review.document_key}`"
                      v-model="rejectionReasons[review.document_key]"
                      rows="2"
                      maxlength="2000"
                      placeholder="e.g. The DTI Certificate is unreadable, and the expiration date printed on it is 2026-04-30, not 2027-04-30."
                      class="mt-1 w-full rounded-md border border-red-300 bg-white px-2 py-1 text-xs text-slate-900 focus:border-red-500 focus:outline-none"
                    />
                    <div class="mt-2 flex gap-2 justify-end">
                      <Button size="sm" variant="ghost" :disabled="acting" @click="cancelRejection">
                        Cancel
                      </Button>
                      <Button
                        size="sm" :disabled="acting || !rejectionReasonFor(review.document_key)"
                        class="bg-red-600 text-white hover:bg-red-700"
                        @click="askReviewDocument(review.document_key, 'rejected')"
                      >
                        {{ acting ? 'Rejecting...' : 'Confirm Rejection' }}
                      </Button>
                    </div>
                  </div>

                  <div v-else class="mt-3 flex gap-2 justify-end">
                    <Button
                      size="sm" variant="ghost" class="text-emerald-700 hover:bg-emerald-50"
                      :disabled="acting" @click="askReviewDocument(review.document_key, 'approved')"
                    >
                      Approve
                    </Button>
                    <Button
                      size="sm" variant="ghost" class="text-red-700 hover:bg-red-50"
                      :disabled="acting" @click="startRejection(review.document_key)"
                    >
                      Reject
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Earlier renewal decisions, kept visible so a repeated rejection
                 is legible as "tried again" rather than looking like one refusal. -->
            <div
              v-else-if="decidedReviews.length"
              class="rounded-lg border border-slate-200 p-4"
            >
              <h3 class="font-bold text-slate-900 text-sm">Renewal Decisions</h3>
              <div class="mt-2 space-y-2">
                <div
                  v-for="review in decidedReviews"
                  :key="review.id"
                  class="rounded-md border border-slate-200 p-2"
                >
                  <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-800">{{ review.label }}</p>
                    <Badge :class="reviewStatusClass(review.status)">{{ review.status }}</Badge>
                  </div>
                  <p class="text-[11px] text-slate-500 mt-0.5">
                    Expiring {{ formatDate(review.expiration_at) }} &middot;
                    decided {{ formatDateTime(review.reviewed_at) }}
                  </p>
                  <p v-if="review.rejection_reason" class="text-[11px] text-red-700 mt-1">
                    {{ review.rejection_reason }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Verification -->
            <div class="rounded-lg border border-slate-200 p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <h3 class="font-bold text-slate-900 text-sm">Date Verification</h3>
                  <p class="text-xs text-slate-500 mt-1">
                    <span v-if="detail.verification.verified_at">
                      Confirmed {{ formatDateTime(detail.verification.verified_at) }} by administrator #{{ detail.verification.verified_by }}.
                    </span>
                    <span v-else-if="pendingReviews.length">
                      Waiting on the renewal decision above. Approving the last pending document confirms these dates.
                    </span>
                    <span v-else>
                      Not yet confirmed. Compare each uploaded file against the expiration date above, then confirm.
                    </span>
                  </p>
                </div>
                <!--
                  Hidden while a review is open rather than disabled: the server
                  refuses it anyway, and a greyed-out button next to the real
                  action invites a click that can only fail.
                -->
                <Button
                  v-if="!pendingReviews.length"
                  size="sm" variant="outline" :disabled="acting" @click="askVerify"
                >
                  Confirm Dates Match
                </Button>
              </div>
            </div>
          </div>

          <!-- Action footer -->
          <div
            v-if="detail"
            class="p-5 border-t border-slate-100 flex flex-wrap gap-2 justify-end bg-slate-50"
          >
            <p
              v-if="actionHint"
              class="w-full text-right text-xs text-slate-500 mb-1"
            >
              {{ actionHint }}
            </p>

            <!--
              One click, every document in the window: the permits normally come
              due together, and a warning that names only one of them reads as
              "the other one is fine".
            -->
            <Button
              variant="outline" :disabled="!detail.permissions.can_notify || acting"
              :title="detail.permissions.can_notify ? '' : 'Nothing is inside the renewal window'"
              @click="askNotify()"
            >
              {{ warnableDocuments.length > 1
                ? `Send Notification (${warnableDocuments.length} documents)`
                : 'Send Notification' }}
            </Button>

            <Button
              v-if="detail.permissions.can_revoke"
              variant="outline"
              class="text-emerald-700 border-emerald-300 hover:bg-emerald-50"
              :disabled="acting"
              @click="revokeOpen = true"
            >
              Revoke Termination
            </Button>

            <Button
              variant="outline"
              class="text-red-700 border-red-300 hover:bg-red-50"
              :disabled="!detail.permissions.can_terminate || acting"
              :title="detail.permissions.can_terminate
                ? ''
                : `Only available ${TERMINATION_GRACE_DAYS} days after expiration`"
              @click="terminateOpen = true"
            >
              Terminate Account
            </Button>

            <Button variant="ghost" @click="showModal = false">Close</Button>
          </div>
        </div>
      </div>
    </Dialog>

    <!--
      Terminate and revoke are the two actions on this screen that suspend or
      restore a business, so they carry their own dialogs with a reason field
      rather than going through the shared confirmation -- there is something to
      type, not just something to agree to.

      Both were hand-rolled fixed overlays wrapped in a <Dialog> that did nothing
      but hold them. ConfirmDialog is the same primitive the rest of the app uses,
      which brings focus trapping, Escape handling and the scroll lock with it.
    -->

    <ConfirmDialog
      :open="revokeOpen"
      title="Restore this account?"
      :description="`${detail?.user?.name} goes back to active status and can trade again immediately. The reversal is recorded against the account, and the termination history is not erased.`"
      confirm-label="Restore account"
      tone="success"
      :busy="acting"
      :confirm-disabled="!reversalReason.trim()"
      @update:open="revokeOpen = $event"
      @confirm="revokeTermination"
    >
      <template #body>
        <div class="mt-4 text-left">
          <Label class="text-slate-500 text-xs mb-1 block">Reason (required)</Label>
          <textarea
            v-model="reversalReason"
            rows="3"
            maxlength="2000"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="e.g. New Mayor's Permit uploaded and verified"
          />
          <p class="mt-1 text-[11px] text-slate-500">
            Required. The server refuses a reversal without one, and the confirm button
            stays disabled until it is typed.
          </p>
        </div>
      </template>
    </ConfirmDialog>

    <ConfirmDialog
      :open="terminateOpen"
      title="Terminate this account?"
      :description="`${detail?.user?.name} is set to inactive and loses access to the platform until you restore it. The account keeps its documents, and can be restored once it submits valid replacements.`"
      confirm-label="Terminate account"
      tone="danger"
      :busy="acting"
      @update:open="terminateOpen = $event"
      @confirm="terminateAccount"
    >
      <template #body>
        <div class="mt-4 text-left">
          <Label class="text-slate-500 text-xs mb-1 block">Reason (optional)</Label>
          <textarea
            v-model="terminationReason"
            rows="3"
            maxlength="2000"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            :placeholder="defaultTerminationReason"
          />
          <p class="mt-1 text-[11px] text-slate-500">
            Shown to the account holder alongside the termination.
          </p>
        </div>
      </template>
    </ConfirmDialog>

    <!--
      The shared confirmation. Every remaining action on this screen is a single
      click that changes somebody's account, and all of them describe themselves
      here first.
    -->
    <ConfirmDialog
      :open="pendingConfirm !== null"
      :title="pendingConfirm?.title ?? ''"
      :description="pendingConfirm?.description ?? ''"
      :confirm-label="pendingConfirm?.confirmLabel ?? 'Confirm'"
      :tone="pendingConfirm?.tone ?? 'default'"
      :busy="acting"
      @update:open="pendingConfirm = $event ? pendingConfirm : null"
      @confirm="runConfirmed"
    >
      <template #body>
        <!--
          The rejection reason, verbatim, exactly as the user will read it. An
          admin refusing a renewal on wording they have not seen is how a user
          re-uploads the same rejected file.
        -->
        <div
          v-if="pendingConfirm?.detail"
          class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-left"
        >
          <p class="text-[11px] font-semibold uppercase tracking-wide text-red-700">
            Reason the user will be shown
          </p>
          <p class="mt-1 text-sm text-red-900">{{ pendingConfirm.detail }}</p>
        </div>

        <p
          v-if="pendingConfirm?.note"
          class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-left text-xs text-slate-600"
        >
          {{ pendingConfirm.note }}
        </p>
      </template>
    </ConfirmDialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { toast } from 'vue-sonner';

import api from '@/utils/axios';
import echo from '@/utils/websocket';

import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table';
import { Dialog } from '@/components/ui/dialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

/**
 * Mirrors App\Support\Documents\DocumentExpiry. The backend is authoritative and
 * re-derives every decision; these two exist so the button the admin sees and the
 * message on it come from the same thresholds. TERMINATION_GRACE_DAYS is used in
 * the disabled-button tooltip, which is exactly the threshold the API enforces.
 */
const RENEWAL_WINDOW_DAYS = 30;
const TERMINATION_GRACE_DAYS = 7;

/**
 * The words sent to the user when an extra document is refused.
 *
 * Named here so the confirmation dialog and the request cannot disagree about what
 * the user is about to be told. It is a single canned reason because refusing an
 * extra document asks one question -- does it match the business details on file
 * -- and a per-document free-text box on this screen was judged more risk than
 * value. Flagged for a follow-up rather than left implicit.
 */
const RELATED_REJECTION_REASON = 'Does not match the submitted business details.';

const rows = ref([]);
const loading = ref(false);
const page = ref(1);
const meta = ref({ total: 0, page: 1, per_page: 15, last_page: 1, pending_review_total: 0 });
const windowDays = ref(RENEWAL_WINDOW_DAYS);
const role = ref('all');
const reviewFilter = ref('all');
const search = ref('');

const showModal = ref(false);
const detail = ref(null);
const detailLoading = ref(false);
const acting = ref(false);

const revokeOpen = ref(false);
const reversalReason = ref('');
const terminateOpen = ref(false);
const terminationReason = ref('');

const relatedRejectionReason = ref('');

/**
 * Which document the admin is currently writing a rejection reason for, and the
 * reasons typed so far.
 *
 * Keyed by document key rather than held in one shared box: a renewal usually has
 * both permits waiting, and a single field would make it look like one reason
 * covers both.
 */
const rejectingKey = ref(null);
const rejectionReasons = reactive({});

/**
 * Client-side text search over the page that was fetched.
 *
 * Deliberately not a server parameter: the endpoint filters on expiration, not on
 * identity, and adding a name filter there would mean a second query per role for a
 * list an admin scans rather than searches.
 */
const filteredRows = computed(() => {
  const term = search.value.trim().toLowerCase();

  if (!term) return rows.value;

  return rows.value.filter((row) =>
    [row.user?.company_name, row.user?.name, row.user?.email]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(term))
  );
});

const trackedDocuments = computed(() => {
  if (!detail.value?.documents) return [];

  return Object.values(detail.value.documents);
});

/**
 * Renewals waiting on a decision, and the ones already decided.
 *
 * The server sends the latest decision per document under `reviews`, which is what
 * the user needs to see. `review_history` is the fuller log the admin may want
 * when a document has been submitted more than once; the pending list is built
 * from history rather than `reviews` so a superseded row never resurfaces as
 * something to act on.
 */
const reviewHistory = computed(() => detail.value?.review_history || []);

const pendingReviews = computed(() =>
  reviewHistory.value.filter((review) => review.status === 'pending')
);

const decidedReviews = computed(() =>
  reviewHistory.value.filter((review) => review.status !== 'pending' && review.status !== 'superseded')
);

function rejectionReasonFor(documentKey) {
  return (rejectionReasons[documentKey] || '').trim();
}

/**
 * The confirmation waiting to be accepted, or null when none is open.
 *
 * One dialog serves every action on this screen rather than a dialog per button.
 * Each of those actions is a single click that changes the account -- promoting a
 * file, telling the user their document was refused, emailing a reminder,
 * stamping a verification, terminating an account -- and a single click is exactly
 * how a wrong one happens. Sharing the dialog means the confirmation wording, the
 * tone, the disabled-while-running behaviour and the "close only on success" rule
 * are written once instead of drifting apart across seven near-identical blocks.
 *
 * `detail` is the rejection reason shown back to the admin before it is sent to
 * the user, verbatim. A refusal the user cannot act on is the one outcome this
 * screen exists to prevent, so the admin reads the exact words first.
 */
const pendingConfirm = ref(null);

/** Queue a confirmation. The action itself is not touched until it is accepted. */
function askConfirm(config) {
  if (acting.value) return;

  pendingConfirm.value = config;
}

/**
 * Run the confirmed action, then close the dialog once the request has settled.
 *
 * It used to close only on success, on the reasoning that the reason typed into a
 * rejection is expensive and a network blip should not cost the admin their place.
 * That protected nothing: the reason lives in the rejection form underneath this
 * dialog, which is not touched here, so staying open only left a stopped spinner
 * and a dialog on screen -- which reads as a hung screen rather than an error. The
 * failure is reported as a toast either way, and retry is one click on the same
 * button with the reason still in place.
 */
async function runConfirmed() {
  const action = pendingConfirm.value;

  if (!action || acting.value) return;

  try {
    await action.run();
  } catch (error) {
    // The action reports its own request failures as a toast, but its
    // post-request bookkeeping can still throw -- reloading the account, mostly --
    // and an exception escaping here would leave the dialog on screen with nothing
    // having happened and no explanation.
    toast.error(readError(error, 'The action could not be completed.'));
  } finally {
    pendingConfirm.value = null;
  }
}

/* -------------------------------------------------------------------------- */
/* Confirmations. Each names the consequence, not just the action.              */
/* -------------------------------------------------------------------------- */

/**
 * Approving hands the account the uploaded file and the date printed on it, and
 * the previous file stays on disk. Rejecting changes nothing about the account,
 * which is the point worth stating: an admin refusing a renewal is not withdrawing
 * a document the business is currently relying on.
 */
function askReviewDocument(documentKey, decision) {
  const approving = decision === 'approved';
  const label = detail.value?.documents?.[documentKey]?.label || 'this document';
  const review = pendingReviews.value.find((r) => r.document_key === documentKey);
  const othersWaiting = pendingReviews.value.length - 1;

  if (!approving && !rejectionReasonFor(documentKey)) {
    toast.error('Give a reason for the rejection so the user knows what to fix.');
    return;
  }

  askConfirm({
    title: approving ? `Approve the ${label}?` : `Reject the ${label}?`,
    description: approving
      ? `The file submitted ${formatDateTime(review?.submitted_at)} becomes the account's live ${label}, replacing the one on file, and the expiry moves to ${formatDate(review?.expiration_at)}.`
      : `The account keeps the ${label} it already has. ${detail.value?.user?.name || 'The user'} is told the reason below and has to upload a replacement.`,
    detail: approving ? null : rejectionReasonFor(documentKey),
    note: approving && othersWaiting > 0
      ? `${othersWaiting} other renewal${othersWaiting === 1 ? ' is' : 's are'} still waiting. The account is stamped as verified only once the last one is approved.`
      : null,
    confirmLabel: approving ? 'Approve document' : 'Reject document',
    tone: approving ? 'success' : 'danger',
    run: () => reviewDocument(documentKey, decision),
  });
}

/**
 * A related document is a stored row with its own approval, so approving or
 * refusing it is the same kind of act as the two permits. The rejection reason is
 * fixed rather than typed, so it is shown here verbatim -- the admin should not be
 * surprised by the words the user is about to read.
 */
function askReviewRelated(doc, decision) {
  const approving = decision === 'approved';
  const canned = RELATED_REJECTION_REASON;

  askConfirm({
    title: approving
      ? `Approve '${doc.document_name}'?`
      : `Reject '${doc.document_name}'?`,
    description: approving
      ? `This document is accepted as part of ${detail.value?.user?.name || 'the account'}'s file.`
      : `This document is refused and ${detail.value?.user?.name || 'the user'} is told the reason below.`,
    detail: approving ? null : canned,
    note: null,
    confirmLabel: approving ? 'Approve document' : 'Reject document',
    tone: approving ? 'success' : 'danger',
    run: () => reviewRelated(doc.id, decision),
  });
}

/**
 * A reminder with no document key warns about everything inside the window, one
 * notification each, so the two permits coming due together cannot leave one of
 * them unmentioned. The dialog says how many will actually go out.
 */
function askNotify(documentKey = null) {
  const targets = documentKey
    ? [detail.value?.documents?.[documentKey]].filter(Boolean)
    : warnableDocuments.value;

  if (!targets.length) return;

  const one = targets.length === 1;

  askConfirm({
    title: one ? 'Send a renewal reminder?' : `Send ${targets.length} renewal reminders?`,
    description:
      `${detail.value?.user?.name || 'This account'} will be emailed about ` +
      `${one ? targets[0].label : 'the ' + targets.length + ' documents inside the renewal window'}. ` +
      'Nothing about the account changes.',
    detail: null,
    note: null,
    confirmLabel: one ? 'Send reminder' : `Send ${targets.length} reminders`,
    tone: 'default',
    run: () => sendNotification(documentKey),
  });
}

/**
 * The stamp records that a human compared each file against its printed date, so
 * the confirmation says that is what is being asserted. It is the only place in
 * the renewal flow where a claim about someone else's paperwork is recorded.
 */
function askVerify() {
  askConfirm({
    title: 'Confirm the dates match?',
    description:
      'This records that you checked every uploaded file against the expiration date printed on it, and stamps the account as verified.',
    detail: null,
    note: null,
    confirmLabel: 'Confirm dates match',
    tone: 'default',
    run: verifyDocuments,
  });
}

function startRejection(documentKey) {
  rejectionReasons[documentKey] = rejectionReasons[documentKey] || '';
  rejectingKey.value = documentKey;
}

function cancelRejection() {
  rejectingKey.value = null;
}

/**
 * Why an action is unavailable, so a disabled button is never just "greyed out".
 */
const actionHint = computed(() => {
  if (!detail.value) return '';

  const { permissions, user, soonest } = detail.value;

  if (permissions.can_revoke) return 'This account is terminated and has since submitted valid documents.';

  if (user?.status === 'inactive') return 'This account is inactive; termination is no longer available.';

  if (permissions.can_terminate) return `Termination is available: the ${soonest?.label} passed the ${TERMINATION_GRACE_DAYS}-day grace period.`;

  if (soonest && soonest.days_until_termination > 0) {
    const doc = trackedDocuments.value.find((d) => d.key === soonest.key);
    return `Termination unlocks in ${soonest.days_until_termination} day(s) — the ${doc?.label ?? soonest.label} expires ${formatDate(soonest.expiration_at)}.`;
  }

  if (detail.value.expiring?.length) return 'A renewal notification can be sent for the documents in the window.';

  return '';
});

const defaultTerminationReason = computed(() => {
  const soonest = detail.value?.soonest;

  if (!soonest) return '';

  return `${soonest.label} expired on ${soonest.expiration_at} and was not renewed within the ${TERMINATION_GRACE_DAYS}-day grace period.`;
});

async function fetchRenewals() {
  loading.value = true;

  try {
    const { data } = await api.get('/admin/renewals', {
      params: {
        page: page.value,
        per_page: 15,
        role: role.value,
        window_days: windowDays.value,
        review: reviewFilter.value
      }
    });

    rows.value = data.data || [];
    meta.value = data.meta || meta.value;

    // The server echoes the window it actually applied, after clamping. Echoing
    // that back keeps the header honest if a value was rejected.
    if (data.window_days) windowDays.value = data.window_days;
  } catch (error) {
    toast.error(readError(error, 'Failed to load renewals.'));
  } finally {
    loading.value = false;
  }
}

function toggleReviewFilter() {
  reviewFilter.value = reviewFilter.value === 'pending' ? 'all' : 'pending';
  page.value = 1;
  fetchRenewals();
}

/**
 * Load an account into the modal.
 *
 * `refresh` reloads the account already on screen instead of opening a different
 * one, and deliberately leaves `detail` in place while it does so.
 *
 * Nulling `detail` on every call looked harmless and was not. Recording a decision
 * makes the server echo `.DocumentReviewDecided` back to the admin's own channel, so
 * the action's reload and the broadcast's reload run at the same time. Whichever
 * nulled last left `detail` empty for the whole round trip, and the follow-up read
 * of `detail.value.user` in whichever action lost the race then threw
 * "Cannot read properties of null (reading 'user')" -- reported as a failure of a
 * decision that had in fact been saved.
 *
 * So the two cases are separated. Opening a different account blanks the modal
 * because the old account's data would be wrong to keep. Refreshing the same one
 * leaves it visible, which also avoids the modal flickering back to a spinner for
 * data that is merely one decision out of date.
 */
async function openDetails(row, { refresh = false } = {}) {
  const accountId = row?.user?.id;

  if (accountId === undefined || accountId === null) return;

  if (!refresh) {
    showModal.value = true;
    detail.value = null;
    relatedRejectionReason.value = '';
    terminationReason.value = '';
    reversalReason.value = '';
    rejectingKey.value = null;

    for (const key of Object.keys(rejectionReasons)) delete rejectionReasons[key];
  }

  detailLoading.value = true;

  try {
    const { data } = await api.get(`/admin/renewals/${accountId}`);
    detail.value = data.data;
  } catch (error) {
    toast.error(readError(error, 'Failed to load account details.'));

    // Only close the modal if it was opened for this load. A failed refresh leaves
    // the account the admin was working on up, which is the state they were in.
    if (!refresh) showModal.value = false;
  } finally {
    detailLoading.value = false;
  }
}

/**
 * Every action funnels through here so a failure surfaces the server's own
 * explanation. That message is not decoration: it is how the API says "you may
 * only terminate 7 days after expiry", and it would otherwise be invisible behind
 * a generic "request failed".
 */
function readError(error, fallback) {
  return (
    error?.response?.data?.message ||
    error?.response?.data?.errors?.[Object.keys(error.response.data.errors || {})[0]]?.[0] ||
    error?.message ||
    fallback
  );
}

async function run(action, successMessage) {
  acting.value = true;

  try {
    const { data } = await action();
    toast.success(data.message || successMessage);
    return data;
  } catch (error) {
    toast.error(readError(error, 'The action could not be completed.'));
    return null;
  } finally {
    acting.value = false;
  }
}

/**
 * Warn the account holder. With no documentKey the backend warns about every
 * document currently inside the renewal window, one notification each, so the
 * two permits coming due together cannot leave one of them unmentioned.
 */
async function sendNotification(documentKey = null) {
  const account = detail.value?.user;

  if (!account) return false;

  if (documentKey && !detail.value?.documents?.[documentKey]) return false;

  const result = await run(
    () => api.post(`/admin/renewals/${account.id}/notify`, documentKey ? { document_key: documentKey } : {}),
    'Notification sent.'
  );

  if (result) fetchRenewals();

  return Boolean(result);
}

async function terminateAccount() {
  // Captured before the request. Read after it instead and the account may be gone
  // from `detail` by then, if the modal was closed or a broadcast reloaded it.
  const account = detail.value?.user;

  if (!account) return false;

  const result = await run(
    () => api.post(`/admin/renewals/${account.id}/terminate`, {
      document_key: detail.value?.soonest?.key,
      reason: terminationReason.value.trim() || undefined
    }),
    'Account terminated.'
  );

  // Closed on success only. The confirm button does not close the dialog by itself,
  // so closing here on failure too would throw away the reason that was just typed
  // and make a failed request look like it had gone through.
  if (result) {
    terminateOpen.value = false;
    terminationReason.value = '';

    await openDetails({ user: account }, { refresh: true });
    fetchRenewals();
  }

  return Boolean(result);
}

async function revokeTermination() {
  const account = detail.value?.user;

  if (!account) return false;

  const result = await run(
    () => api.post(`/admin/renewals/${account.id}/revoke-termination`, {
      reversal_reason: reversalReason.value.trim()
    }),
    'Termination revoked.'
  );

  if (result) {
    revokeOpen.value = false;
    reversalReason.value = '';
    await openDetails({ user: account }, { refresh: true });
    fetchRenewals();
  }

  return Boolean(result);
}

async function verifyDocuments() {
  const account = detail.value?.user;

  if (!account) return false;

  const result = await run(
    () => api.post(`/admin/renewals/${account.id}/verify-documents`, {}),
    'Documents verified.'
  );

  // Not awaited: there is nothing to report from a reload, and the admin should see
  // the new state as soon as it lands rather than after the spinner.
  if (result) openDetails({ user: account }, { refresh: true });

  return Boolean(result);
}

/**
 * Approve or reject one renewed document.
 *
 * Approval is a single click, because a renewal normally has two permits waiting
 * and an admin is reading each file once. Rejection is two steps by design: it
 * notifies the user, and a reason typed on autopilot is worse than no reason.
 */
async function reviewDocument(documentKey, decision) {
  const rejecting = decision === 'rejected';
  const reason = rejectionReasonFor(documentKey);

  if (rejecting && !reason) {
    toast.error('Give a reason for the rejection so the user knows what to fix.');
    return;
  }

  // Captured before the request, for the reason given in terminateAccount: this
  // decision triggers a broadcast that reloads the same account, and reading
  // `detail` after the round trip is what used to throw.
  const account = detail.value?.user;

  if (!account) return;

  const label = detail.value?.documents?.[documentKey]?.label || 'Document';

  const result = await run(
    () => api.post(`/admin/renewals/${account.id}/documents/${documentKey}/review`, {
      decision,
      rejection_reason: rejecting ? reason : null
    }),
    `${label} ${decision}.`
  );

  if (!result) return;

  rejectingKey.value = null;
  delete rejectionReasons[documentKey];

  // Reloaded rather than patched in place: approving the last pending document
  // also stamps the account's verification, and the permissions and row in the
  // table behind this modal both change with it.
  await openDetails({ user: account }, { refresh: true });
  fetchRenewals();

  return true;
}

function reviewStatusClass(status) {
  switch (status) {
    case 'approved': return 'bg-emerald-100 text-emerald-700';
    case 'rejected': return 'bg-red-100 text-red-700';
    case 'superseded': return 'bg-slate-100 text-slate-500';
    default: return 'bg-amber-100 text-amber-800';
  }
}

async function reviewRelated(documentId, decision) {
  const account = detail.value?.user;

  if (!account) return false;

  const result = await run(
    () => api.post(`/admin/renewals/${account.id}/related-documents/${documentId}/review`, {
      decision,
      rejection_reason: decision === 'rejected' ? RELATED_REJECTION_REASON : null
    }),
    `Document ${decision}.`
  );

  if (result) openDetails({ user: account }, { refresh: true });

  return Boolean(result);
}

/* Presentation helpers. Formatting only -- no rule is decided here. */

function countdown(days) {
  if (days === null || days === undefined) return '—';

  if (days < 0) return `${Math.abs(days)} day(s) ago`;

  if (days === 0) return 'today';

  if (days === 1) return 'tomorrow';

  return `in ${days} day(s)`;
}

function stateLabel(doc) {
  switch (doc.state) {
    case 'expired': return 'Expired';
    case 'critical': return 'Urgent';
    case 'warning': return 'Nearing expiry';
    case 'missing': return 'No date';
    default: return 'Valid';
  }
}

function stateClass(state) {
  switch (state) {
    case 'expired': return 'bg-red-100 text-red-700 border border-red-200';
    case 'critical': return 'bg-red-50 text-red-700 border border-red-200';
    case 'warning': return 'bg-amber-100 text-amber-800 border border-amber-200';
    case 'missing': return 'bg-slate-100 text-slate-600';
    default: return 'bg-emerald-100 text-emerald-700';
  }
}

function docBorder(doc) {
  if (doc.state === 'expired' || doc.state === 'critical') return 'border-red-300 bg-red-50/40';
  if (doc.state === 'warning') return 'border-amber-300 bg-amber-50/40';
  return 'border-slate-200';
}

function statusClass(row) {
  if (row.termination) return 'bg-red-100 text-red-700';
  if (row.user.status === 'inactive') return 'bg-slate-200 text-slate-600';
  if (row.is_overdue) return 'bg-orange-100 text-orange-800';
  return 'bg-amber-100 text-amber-800';
}

function statusLabel(row) {
  if (row.termination) return 'Terminated';
  if (row.user.status === 'inactive') return 'Inactive';
  if (row.is_overdue) return 'Overdue';
  return 'Active';
}

function relatedStatusClass(status) {
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700';
  if (status === 'rejected') return 'bg-red-100 text-red-700';
  return 'bg-amber-100 text-amber-800';
}

/**
 * The documents inside the renewal window, i.e. the ones a bulk "Send
 * Notification" click will actually warn about.
 */
const warnableDocuments = computed(() => trackedDocuments.value.filter(isWarnable));

/**
 * Whether the "Warn about <document>" button applies, mirroring the server's
 * needsRenewal() (a dated document with 30 days or fewer left). The backend
 * re-checks this regardless -- the button is a convenience, not the gate.
 */
function isWarnable(doc) {
  return doc.days_remaining !== null && doc.days_remaining <= RENEWAL_WINDOW_DAYS;
}

function isImage(path) {
  return /\.(png|jpe?g|gif|webp|bmp)$/i.test(path || '');
}

/**
 * Date-only strings are rendered without going through `new Date`.
 *
 * `new Date('2027-01-30')` is parsed as UTC midnight, so in any timezone west of
 * Greenwich it displays as the previous day. Splitting the string avoids that and
 * needs no timezone maths at all.
 */
function formatDate(value) {
  if (!value) return 'Not set';

  const [year, month, day] = String(value).slice(0, 10).split('-');

  if (!year || !month || !day) return String(value);

  return new Date(Number(year), Number(month) - 1, Number(day)).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function formatDateTime(value) {
  if (!value) return 'Not set';

  return new Date(value).toLocaleString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
  });
}

watch([role, windowDays], () => {
  page.value = 1;
  fetchRenewals();
});

/**
 * Live updates for the review queue.
 *
 * Both events refetch rather than patching the table. The pushed payload is
 * scoped to one account, and this list is a windowed page across every account
 * in the system -- an approved renewal can pull a row out of the window
 * entirely, or move it between pages. Deciding which rows that is belongs to the
 * server, so it is asked. One refetch on an event that fires once per human
 * decision is cheap; guessing wrong about membership would leave an admin
 * looking at an account they can no longer act on.
 *
 * No toast here. The admin who clicked already got one from the action itself,
 * and a broadcast has no way to tell their own decision apart from someone
 * else's -- so any message would double up for the person who acted. The row
 * appearing or disappearing is the notification for everyone else.
 */
onMounted(() => {
  const channel = echo.private('admin.renewals');

  channel.listen('.DocumentsRenewed', () => {
    fetchRenewals();
  });

  channel.listen('.DocumentReviewDecided', async (e) => {
    fetchRenewals();

    // Only when it is this account that was decided. Reloading on any decision
    // would yank the open modal to a different supplier's account, replacing
    // whatever the admin was in the middle of reading.
    //
    // A refresh, not a re-open, for the reason given on openDetails: the admin who
    // just pressed Approve is on this channel too, so this usually fires alongside
    // the action's own reload of the very same account.
    if (detail.value && detail.value.user?.id === e?.user_id) {
      await openDetails({ user: { id: e.user_id } }, { refresh: true });
    }
  });
});

onBeforeUnmount(() => {
  echo.leave('admin.renewals');
});

fetchRenewals();
</script>
