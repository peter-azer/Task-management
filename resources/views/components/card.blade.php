@props(['teamid'])

<template id="card" class=" !bg-gray-500 ">
    <div data-role="card" draggable="true"
        class="w-full px-4 py-2 overflow-hidden text-sm bg-white border border-gray-200 cursor-pointer select-none line-clamp-3 rounded-xl">
    </div>
</template>

@pushOnce('component')
<Script>
    const cardTemplate = document.querySelector("template#card");
    class Card {
        constructor(id, name, members, board) {
            this.board = board;
            const content = cardTemplate.content.cloneNode(true);
            const node = document.createElement("div");
            node.append(content);
            this.ref = node.children[0];

            console.log("Card created", id, name, members);

            const avatarsHtml = `
  <div class="flex justify-start gap-3">
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
  </div>
`;
            this.ref.innerHTML = `${name} ${avatarsHtml}`;
            this.ref.dataset.id = id;
            this.ref.setAttribute('draggable', (id != null));

            this.ref.addEventListener("dragstart", () => {
                this.board.IS_EDITING = true;
                this.ref.classList.add("is-dragging");
                this.ref.classList.toggle("!bg-gray-500");
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

                ServerRequest.post(`{{ url('team/'.$teamid.'/board/${board_id}/card/reorder') }}`, {
                        column_id: this.ref.closest("div[data-role='column']").dataset.id,
                        middle_id: this.ref.dataset.id,
                        bottom_id: this.ref.nextElementSibling?.dataset?.id || null,
                        top_id: this.ref.previousElementSibling?.dataset?.id || null,
                    })
                    .then((response) => {
                        this.board.IS_EDITING = false;
                        this.ref.setAttribute('draggable', true);
                        console.log(response.data);
                    });
            })
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
</Script>
@endPushOnce