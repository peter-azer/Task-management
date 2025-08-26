@props(['teamid', 'teamMembers' => [], 'owner' => null])

<template id="card" class="!bg-gray-500">
    <div data-role="card" draggable="true"
        class="w-full px-4 py-2 overflow-hidden text-sm bg-white border border-gray-200 cursor-pointer select-none line-clamp-3 rounded-xl">
    </div>
</template>

@pushOnce('component')
<script>
    const TEAM_ID = @json($teamid);
    const cardTemplate = document.querySelector("template#card");

    // ---------- helpers ----------
    const EDIT_FORM_SELECTOR = "[data-role='edit-card-form']";

    function buildUpdateCardUrl(teamId, boardId, cardId) {
        return `/team/${teamId}/board/${boardId}/card/${cardId}/update`;
    }

    // Wait for an element to exist (useful for portaled modals)
    function waitForElement(selector, timeoutMs = 5000) {
        return new Promise((resolve, reject) => {
            const start = performance.now();
            (function check() {
                const el = document.querySelector(selector);
                if (el) return resolve(el);
                if (performance.now() - start > timeoutMs) return reject(new Error(`Timeout waiting for ${selector}`));
                requestAnimationFrame(check);
            })();
        });
    }

    // Format JS Date to 'YYYY-MM-DDTHH:mm' in LOCAL time (no UTC shift)
    function toLocalDatetimeValue(date) {
        if (!date) return "";
        const d = (date instanceof Date) ? date : new Date(date);
        if (isNaN(d)) return "";
        const pad = (n) => String(n).padStart(2, "0");
        const yyyy = d.getFullYear();
        const mm = pad(d.getMonth() + 1);
        const dd = pad(d.getDate());
        const hh = pad(d.getHours());
        const mi = pad(d.getMinutes());
        return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
    }

    class Card {
        /**
         * @param {number|string} id
         * @param {string} name
         * @param {Array} members
         * @param {string|Date|null} start_date
         * @param {string|Date|null} end_date
         * @param {0|1|boolean} is_done
         * @param {object} board - object with `ref` pointing to the board DOM element
         * @param {string} description
         */
        constructor(id, name, members, start_date, end_date, is_done, board, description) {
            this.id = id;
            this.name = name ?? "";
            this.members = members ?? [];
            this.start_date = start_date ?? null;
            this.end_date = end_date ?? null;
            this.is_done = (is_done === 1 || is_done === true);
            this.board = board;
            this.description = description ?? "";

            const content = cardTemplate.content.cloneNode(true);
            const wrapper = document.createElement("div");
            wrapper.append(content);
            this.ref = wrapper.children[0];

            this.render();
            this.attachEvents();
        }

        render() {
            const now = new Date();
            const startDate = this.start_date ? new Date(this.start_date) : null;
            const endDate = this.end_date ? new Date(this.end_date) : null;
            const hasDates = startDate instanceof Date && !isNaN(startDate) && endDate instanceof Date && !isNaN(endDate);

            let datesHtml = "";
            if (!hasDates) {
                datesHtml = `
                    <div class="mt-2 p-2 bg-white text-gray-600 rounded-md text-xs">
                        No dates assigned
                    </div>
                `;
            } else {
                const isLate = (now > endDate) && !this.is_done;
                console.log(this.is_done);
                const statusText = this.is_done == true ? '✅' : (isLate ? '⛔' : '⏳');
                const bgClass = this.is_done == true ? 'bg-green-100' : (isLate ? 'bg-red-100' : 'bg-yellow-100');
                const textClass = this.is_done == true ? 'text-green-700' : (isLate ? 'text-red-700' : 'text-yellow-700');

                const pretty = (d) => {
                    const opts = {
                        month: 'short',
                        day: 'numeric'
                    };
                    const timeNeeded = d.getHours() || d.getMinutes();
                    const t = timeNeeded ? ` - ${d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}` : '';
                    return `${d.toLocaleDateString(undefined, opts)}${t}`;
                };

                datesHtml = `
                    <div class="py-4 rounded-lg space-y-3">
                        <div class="relative flex justify-start gap-3 items-center">
                            ${
                                (this.members ?? []).map(m => {
                                    const initials = (m?.name ?? "")
                                        .split(" ")
                                        .map(p => p[0])
                                        .join("")
                                        .substring(0, 2)
                                        .toUpperCase();

                                    return m?.image_path
                                        ? `<div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white">
                                              <img src="/${m.image_path}" alt="${m.name ?? ''}" class="object-cover w-full h-full" />
                                           </div>`
                                        : `<div class="w-8 h-8 flex items-center justify-center rounded-full bg-black text-white text-xs font-bold border-2 border-white">
                                              ${initials || "?"}
                                           </div>`;
                                }).join('')
                            }
                            <div class="absolute right-0 text-xl">${statusText}</div> 
                        </div>
                        <div class="flex flex-wrap items-center text-xs gap-2 px-4 py-2 ${bgClass} ${textClass}">
                            <div>${pretty(startDate)}</div>
                            <div>${pretty(endDate)}</div>
                        </div>
                    </div>
                `;
            }

            // Card DOM
            this.ref.innerHTML = `
                <div class="relative p-2 hover:bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <input 
                            type="checkbox" 
                            name="is_done"
                            class="task-done-checkbox accent-green-600" 
                            ${this.is_done ? "checked" : ""}
                            onclick="event.stopPropagation()" 
                        />
                        <span class="font-medium">${this.name}</span>
                    </div>
                    ${datesHtml}

                    <!-- hover action buttons -->
                    <div class="absolute top-2 right-2 flex gap-2 opacity-0 transition" data-role="card-actions">
                        ${this.renderActionButtons()}
                    </div>
                </div>
            `;

            this.ref.dataset.id = this.id;
            this.ref.setAttribute('draggable', (this.id != null));
        }

        attachEvents() {
            const actions = this.ref.querySelector("[data-role='card-actions']");
            this.ref.addEventListener("mouseenter", () => {
                actions.classList.remove("opacity-0");
                actions.classList.add("opacity-100");
            });
            this.ref.addEventListener("mouseleave", () => {
                actions.classList.remove("opacity-100");
                actions.classList.add("opacity-0");
            });

            // Navigate on card click
            this.ref.addEventListener("click", () => {
                const board_id = this.board.ref.dataset.id;
                const card_id = this.ref.dataset.id;
                window.location.href = `{{ url('team/'.$teamid.'/board/${board_id}/card/${card_id}/view') }}`;
            });

            // Dragging behaviour
            let originalColumn = null;
            this.ref.addEventListener("dragstart", () => {
                this.board.IS_EDITING = true;
                this.ref.classList.add("is-dragging");
                this.ref.classList.toggle("!bg-gray-500");
                originalColumn = this.ref.closest("div[data-role='column']");
            });

            this.ref.addEventListener("dragend", () => {
                this.ref.classList.remove("is-dragging");
                this.ref.setAttribute('draggable', false);
                this.ref.classList.toggle("!bg-gray-500");

                const board_id = this.board.ref.dataset.id;
                const newColumn = this.ref.closest("div[data-role='column']");
                const currentColId = newColumn?.dataset?.id;
                const originalColId = originalColumn?.dataset?.id;

                if (originalColId === currentColId) {
                    const container = originalColumn.querySelector("section > div#card-container");
                    const before = this.ref.previousElementSibling;
                    if (before) container.insertBefore(this.ref, before.nextSibling);
                    else container.prepend(this.ref);
                    this.board.IS_EDITING = false;
                    this.ref.setAttribute('draggable', true);
                    return;
                }

                ServerRequest.post(`{{ url('team/'.$teamid.'/board/${board_id}/card/reorder') }}`, {
                    column_id: currentColId,
                    middle_id: this.ref.dataset.id,
                    bottom_id: this.ref.nextElementSibling?.dataset?.id || null,
                    top_id: this.ref.previousElementSibling?.dataset?.id || null,
                }).then(() => {
                    this.board.IS_EDITING = false;
                    this.ref.setAttribute('draggable', true);
                });
            });

            // Checkbox: done/undone
            const checkbox = this.ref.querySelector(".task-done-checkbox");
            checkbox.addEventListener("change", (e) => {
                const isChecked = e.target.checked;
                const board_id = this.board.ref.dataset.id;
                const card_id = this.ref.dataset.id;

                ServerRequest.post(`{{ url('team/'.$teamid.'/board') }}/${board_id}/card/${card_id}/done`, {
                    is_done: isChecked ? 1 : 0,
                }).catch(err => {
                    console.error("Error updating task status", err);
                });
            });

            // Action buttons
            actions.querySelectorAll("button").forEach(btn => {
                btn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    const action = btn.dataset.action;
                    if (action === "assign") this.openAssignModal();
                    if (action === "edit") this.openEditModal();
                    if (action === "delete") this.openDeleteModal();
                });
            });
        }

        openEditModal() {
            const board_id = this.board.ref.dataset.id;

            // Show your modal (no assumptions about events)
            ModalView.show('editCard');

            // Wait for form to be mounted into DOM, then fill it
            waitForElement(EDIT_FORM_SELECTOR, 5000)
                .then((form) => {
                    // set action
                    form.action = buildUpdateCardUrl(TEAM_ID, board_id, this.id);

                    // fill values
                    const nameInput = form.querySelector("[name='card_name']");
                    const descInput = form.querySelector("[name='card_description']");
                    const startInput = form.querySelector("[name='start_date'], #edit_start_date");
                    const endInput = form.querySelector("[name='end_date'], #edit_end_date");

                    if (nameInput) nameInput.value = this.name ?? "";
                    if (descInput) descInput.value = this.description ?? "";
                    if (startInput) startInput.value = toLocalDatetimeValue(this.start_date);
                    if (endInput) endInput.value = toLocalDatetimeValue(this.end_date);
                })
                .catch((err) => {
                    console.error("Edit modal form not found:", err);
                });
        }

        openAssignModal() {
            const board_id = this.board.ref.dataset.id;

            // Store current card reference for modal access
            window.currentCard = this;

            // Open assign task modal
            ModalView.show('assignTask');

            // Wait for forms to be mounted into DOM, then set the actions
            waitForElement("[data-role='assign-task-form']", 5000)
                .then((assignForm) => {
                    // Build the assign task URL
                    assignForm.action = `/team/${TEAM_ID}/board/${board_id}/card/${this.id}/assignTask`;

                    // Reset assign form state
                    const assignSelect = assignForm.querySelector('select[name="id"]');
                    if (assignSelect) assignSelect.value = '';
                })
                .catch((err) => {
                    console.error("Assign modal form not found:", err);
                    if (typeof ToastView !== 'undefined') {
                        ToastView.notif('Error', 'Failed to load assign form');
                    }
                });

            waitForElement("[data-role='unassign-task-form']", 5000)
                .then((unassignForm) => {
                    // Build the unassign task URL
                    unassignForm.action = `/team/${TEAM_ID}/board/${board_id}/card/${this.id}/unassignTask`;

                    // Reset unassign form state and populate with current members
                    const unassignSelect = unassignForm.querySelector('select[name="id"]');
                    if (unassignSelect) {
                        unassignSelect.value = '';

                        // Clear existing options except the first one
                        while (unassignSelect.children.length > 1) {
                            unassignSelect.removeChild(unassignSelect.lastChild);
                        }

                        // Populate with current card members
                        if (this.members && this.members.length > 0) {
                            this.members.forEach(member => {
                                const option = document.createElement('option');
                                option.value = member.id;
                                option.textContent = member.name;
                                unassignSelect.appendChild(option);
                            });
                        } else {
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'No members assigned to this task';
                            option.disabled = true;
                            unassignSelect.appendChild(option);
                        }
                    }
                })
                .catch((err) => {
                    console.error("Unassign modal form not found:", err);
                    if (typeof ToastView !== 'undefined') {
                        ToastView.notif('Error', 'Failed to load unassign form');
                    }
                });
        }

        renderActionButtons() {
            const isOwnerOrAdmin = @json(isset($owner) && (Auth::user()->id == $owner->id || Auth::user()->hasRole('super-admin')));
            if (!isOwnerOrAdmin) return '';

            return `
                <button class="p-1 rounded-full bg-gray-200 hover:bg-green-500 hover:text-white" title="Assign/Unassign Member" data-action="assign">👥</button>
                <button class="p-1 rounded-full bg-gray-200 hover:bg-blue-500 hover:text-white" title="Edit" data-action="edit">✏️</button>
                <button class="p-1 rounded-full bg-gray-200 hover:bg-red-500 hover:text-white" title="Delete" data-action="delete">🗑️</button>
            `;
        }


        openDeleteModal() {
            const board_id = this.board.ref.dataset.id;

            // Open delete card modal
            ModalView.show('deleteCard');

            // Wait for form to be mounted into DOM, then set the action
            waitForElement("[data-role='delete-card-form']", 5000)
                .then((form) => {
                    // Build the delete card URL
                    form.action = `/team/${TEAM_ID}/board/${board_id}/card/${this.id}/delete`;
                })
                .catch((err) => {
                    console.error("Delete modal form not found:", err);
                    if (typeof ToastView !== 'undefined') {
                        ToastView.notif('Error', 'Failed to load delete form');
                    }
                });
        }

        setId(id) {
            this.ref.dataset.id = id;
            this.id = id;
            this.ref.setAttribute('draggable', true);
        }

        mountTo(column) {
            column.ref.querySelector("section > div#card-container").append(this.ref);
            this.board = column.board;
        }
    }
</script>
@endpushOnce

{{-- Delete Card Modal Template --}}
@if (isset($owner) && (Auth::user()->id == $owner->id || Auth::user()->hasRole('super-admin')))
<template is-modal="deleteCard">
    <form class="flex flex-col items-center justify-center w-full h-full gap-6 p-4" method="POST" data-role="delete-card-form">
        @csrf
        <input type="hidden" name="id" value="{{ Auth::user()->id }}">
        <div class="text-red-600 mb-4">
            <x-fas-exclamation-triangle class="w-6 h-6 mx-auto" />
        </div>
        <p class="mb-6 text-lg text-center">Are you sure you want to delete this card?</p>
        <div class="flex gap-6">
            <x-form.button type="submit">Yes</x-form.button>
            <x-form.button type="button" action="ModalView.close()" primary>No</x-form.button>
        </div>
    </form>
</template>
@endif

{{-- Assign/Unassign Task Modal Template --}}
@if (isset($owner) && (Auth::user()->id == $owner->id || Auth::user()->hasRole('super-admin')))
<template is-modal="assignTask">
    <div class="flex flex-col items-center justify-center w-full h-full gap-6 p-4">
        <p class="mb-6 text-lg text-center">Manage Task Assignment</p>

        {{-- Assign Form --}}
        <form class="flex flex-col items-center justify-center w-full gap-6" method="POST" data-role="assign-task-form">
            @csrf
            <p class="text-md text-center">Assign member to task</p>
            <select name="id" id="assign_member_id" class="w-full p-2 border-2 border-gray-200 rounded" required>
                <option value="">Select a team member to assign</option>
                @if(isset($teamMembers) && count($teamMembers) > 0)
                @foreach ($teamMembers as $member)
                <option value="{{ $member->id }}">{{ $member->name }}</option>
                @endforeach
                @else
                <option value="" disabled>No team members available</option>
                @endif
            </select>
            <x-form.button type="submit" class="w-full">Assign Member</x-form.button>
        </form>

        <hr class="w-full border-gray-300">

        {{-- Unassign Form --}}
        <form class="flex flex-col items-center justify-center w-full gap-6" method="POST" data-role="unassign-task-form">
            @csrf
            <p class="text-md text-center">Remove member from task</p>
            <select name="id" id="unassign_member_id" class="w-full p-2 border-2 border-gray-200 rounded" required>
                <option value="">Select assigned member to remove</option>
                {{-- This will be populated dynamically with assigned members --}}
            </select>
            <x-form.button type="submit" class="w-full bg-orange-500 hover:bg-orange-600">Unassign Member</x-form.button>
        </form>

        <x-form.button type="button" action="ModalView.close()" class="w-full">Cancel</x-form.button>
    </div>
</template>
@endif

<script>
    // Add event listeners for modals
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ModalView !== 'undefined') {
            // Delete card modal
            ModalView.onShow('deleteCard', (modal) => {
                modal.querySelectorAll("form[data-role='delete-card-form']").forEach(
                    form => form.addEventListener("submit", () => {
                        if (typeof PageLoader !== 'undefined') {
                            PageLoader.show();
                        }
                    })
                );
            });

            // Assign task modal
            ModalView.onShow('assignTask', (modal) => {
                // Get the card ID from the currently clicked card
                const cardElement = document.querySelector('.is-dragging') || document.querySelector('[data-role="card"]:hover');
                let currentCardMembers = [];

                if (cardElement) {
                    const cardId = cardElement.dataset.id;
                    // Find the card object to get its members
                    const board = window.board || {};
                    if (board.columnList) {
                        board.columnList.forEach(column => {
                            if (column.cardList) {
                                column.cardList.forEach(card => {
                                    if (card.id == cardId) {
                                        currentCardMembers = card.members || [];
                                    }
                                });
                            }
                        });
                    }
                }

                // Populate unassign dropdown with assigned members when modal opens
                const unassignSelect = modal.querySelector('select[name="id"]#unassign_member_id');
                if (unassignSelect) {
                    // Clear existing options except the first one
                    while (unassignSelect.children.length > 1) {
                        unassignSelect.removeChild(unassignSelect.lastChild);
                    }

                    // Populate with only assigned members
                    if (currentCardMembers && currentCardMembers.length > 0) {
                        currentCardMembers.forEach(member => {
                            const option = document.createElement('option');
                            option.value = member.id;
                            option.textContent = member.name;
                            unassignSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No members assigned to this task';
                        option.disabled = true;
                        unassignSelect.appendChild(option);
                    }
                }

                // Handle assign form submission
                modal.querySelectorAll("form[data-role='assign-task-form']").forEach(
                    form => {
                        form.addEventListener("submit", (e) => {
                            const selectElement = form.querySelector('select[name="id"]');
                            if (!selectElement || !selectElement.value) {
                                e.preventDefault();
                                if (typeof ToastView !== 'undefined') {
                                    ToastView.notif('Warning', 'Please select a team member to assign');
                                }
                                return false;
                            }
                            if (typeof PageLoader !== 'undefined') {
                                PageLoader.show();
                            }
                        });
                    }
                );

                // Handle unassign form submission
                modal.querySelectorAll("form[data-role='unassign-task-form']").forEach(
                    form => {
                        form.addEventListener("submit", (e) => {
                            const selectElement = form.querySelector('select[name="id"]');
                            if (!selectElement || !selectElement.value) {
                                e.preventDefault();
                                if (typeof ToastView !== 'undefined') {
                                    ToastView.notif('Warning', 'Please select a member to unassign');
                                }
                                return false;
                            }
                            if (typeof PageLoader !== 'undefined') {
                                PageLoader.show();
                            }
                        });
                    }
                );
            });
        }
    });
</script>