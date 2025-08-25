@props(['teamid'])

<template id="card" class="!bg-gray-500">
    <div data-role="card" draggable="true"
        class="w-full px-4 py-2 overflow-hidden text-sm bg-white border border-gray-200 cursor-pointer select-none line-clamp-3 rounded-xl">
    </div>
</template>

@pushOnce('component')
<script>
    const cardTemplate = document.querySelector("template#card");

    class Card {
        constructor(id, name, members, start_date, end_date, is_done, board) {
            this.board = board;
            const content = cardTemplate.content.cloneNode(true);
            const node = document.createElement("div");
            node.append(content);
            this.ref = node.children[0];

            let originalColumn = null;

            const now = new Date();
            const startDate = start_date ? new Date(start_date) : null;
            const endDate = end_date ? new Date(end_date) : null;
            const isDone = is_done;

            const hasDates = startDate instanceof Date && !isNaN(startDate) &&
                endDate instanceof Date && !isNaN(endDate);

            let avatarsHtml = "";

            if (!hasDates) {
                avatarsHtml = `
                    <div class="mt-2 p-2 bg-white text-gray-600 rounded-md text-xs">
                        No dates assigned
                    </div>
                `;
            } else {
                const isLate = now > endDate && isDone == false;

                const statusText = isDone == 1 ? '✅' : isLate ? '⛔' : '⏳';
                const bgClass = isDone == 1 ? 'bg-green-100' : isLate ? 'bg-red-100' : 'bg-yellow-100';
                const textClass = isDone == 1 ? 'text-green-700' : isLate ? 'text-red-700' : 'text-yellow-700';

                const formatDate = (date) => {
                    const options = {
                        month: 'short',
                        day: 'numeric'
                    };
                    const timeString = date.getHours() || date.getMinutes() ?
                        ` - ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}` :
                        '';
                    return `${date.toLocaleDateString(undefined, options)}${timeString}`;
                };

                avatarsHtml = `
                    <div class="py-4 rounded-lg space-y-3">
                        <div class="relative flex justify-start gap-3 items-center">
                            ${
                                (members ?? []).map(m => {
                                    const initials = m?.name?.split(" ").map(p => p[0]).join("").substring(0, 2).toUpperCase();
                                    return m?.image_path
                                        ? `<div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white">
                                            <img src="/${m.image_path}" alt="${m.name}" class="object-cover w-full h-full" />
                                        </div>`
                                        : `<div class="w-8 h-8 flex items-center justify-center rounded-full bg-black text-white text-xs font-bold border-2 border-white">
                                            ${initials}
                                        </div>`;
                                }).join('')
                            }
                            <div class="absolute right-0 text-xl">${statusText}</div> 
                        </div>
                        <div class="flex flex-wrap justify-left items-center text-xs gap-2 px-4 py-2 ${bgClass} ${textClass}">
                            <div>${formatDate(startDate)}</div>
                            <div>${formatDate(endDate)}</div>
                        </div>
                    </div>
                `;
            }

            // Main card HTML
            this.ref.innerHTML = `
                <div class="relative p-2 hover:bg-gray-50 rounded-lg">
                    <!-- Card Content -->
                    <div class="flex items-center gap-2">
                        <input 
                            type="checkbox" 
                            name="is_done"
                            class="task-done-checkbox accent-green-600" 
                            ${is_done == 1 ? "checked" : ""}
                            onclick="event.stopPropagation()" 
                        />
                        <span class="font-medium">${name}</span>
                    </div>
                    ${avatarsHtml}
                </div>
            `;

            // === Action buttons per card ===
            const actions = document.createElement("div");
            actions.className = "absolute top-2 right-2 flex gap-2 opacity-0 transition";
            actions.setAttribute("id", `card-actions-${id}`);
            actions.innerHTML = `
                <button 
                    class="p-1 rounded-full bg-gray-200 hover:bg-blue-500 hover:text-white"
                    title="Edit"
                >✏️</button>
                <button 
                    class="p-1 rounded-full bg-gray-200 hover:bg-red-500 hover:text-white"
                    title="Delete"
                >🗑️</button>

                
            `;

            actions.querySelectorAll("button").forEach(btn => {
                btn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    if (btn.title === "Edit") {
                        console.log("Edit card", id);
                        // ModalView.show('editCard', { id })
                    } else {
                        console.log("Delete card", id);
                        // ModalView.show('deleteCard', { id })
                    }
                });
            });

            // Append actions to this card only
            this.ref.querySelector("div.relative").append(actions);

            // === Independent hover handling per card ===
            this.ref.addEventListener("mouseenter", () => {
                actions.classList.remove("opacity-0");
                actions.classList.add("opacity-100");
            });
            this.ref.addEventListener("mouseleave", () => {
                actions.classList.remove("opacity-100");
                actions.classList.add("opacity-0");
            });

            this.ref.dataset.id = id;
            this.ref.setAttribute('draggable', (id != null));

            this.ref.addEventListener("dragstart", () => {
                this.board.IS_EDITING = true;
                this.ref.classList.add("is-dragging");
                this.ref.classList.toggle("!bg-gray-500");
                originalColumn = this.ref.closest("div[data-role='column']");
            });

            this.ref.addEventListener("click", () => {
                const board_id = this.board.ref.dataset.id;
                const card_id = this.ref.dataset.id;
                window.location.href = `{{ url('team/'.$teamid.'/board/${board_id}/card/${card_id}/view') }}`;
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
                    if (before) {
                        container.insertBefore(this.ref, before.nextSibling);
                    } else {
                        container.prepend(this.ref);
                    }

                    this.board.IS_EDITING = false;
                    this.ref.setAttribute('draggable', true);
                    return;
                }

                ServerRequest.post(`{{ url('team/'.$teamid.'/board/${board_id}/card/reorder') }}`, {
                    column_id: currentColId,
                    middle_id: this.ref.dataset.id,
                    bottom_id: this.ref.nextElementSibling?.dataset?.id || null,
                    top_id: this.ref.previousElementSibling?.dataset?.id || null,
                }).then((response) => {
                    this.board.IS_EDITING = false;
                    this.ref.setAttribute('draggable', true);
                    console.log(response.data);
                });
            });

            const checkbox = this.ref.querySelector(".task-done-checkbox");
            checkbox.addEventListener("change", (e) => {
                const isChecked = e.target.checked;
                const board_id = this.board.ref.dataset.id;
                const card_id = this.ref.dataset.id;

                ServerRequest.post(`{{ url('team/'.$teamid.'/board') }}/${board_id}/card/${card_id}/done`, {
                    is_done: isChecked === true ? 1 : 0,
                }).then(response => {
                    console.log("Task done status updated", response.data);
                }).catch(err => {
                    console.error("Error updating task status", err);
                });
            });
        }

        setId(id) {
            this.ref.dataset.id = id;
            this.ref.setAttribute('draggable', true);
        }

        mountTo(column) {
            column.ref.querySelector("section > div#card-container").append(this.ref);
            this.board = column.board;
        }
    }
</script>
@endpushOnce