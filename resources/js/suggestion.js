import tippy from "tippy.js";

const renderSuggestionsComponent = (items) => {
    let filteredItems = [];

    Alpine.store('filamentCommentsMentionsFiltered', {
        items: [],
    });



    return {
        items: ({ query }) => {
            filteredItems = items
                .filter(item => item.name.toLowerCase().startsWith(query.toLowerCase()))
                .slice(0, 5);

            Alpine.store('filamentCommentsMentionsFiltered').items = filteredItems;

            return filteredItems
        },

        command: ({ editor, range, props }) => {
            // increase range.to by one when the next node is of type "text"
            // and starts with a space character
            const nodeAfter = editor.view.state.selection.$to.nodeAfter
            const overrideSpace = nodeAfter?.text?.startsWith(' ')

            // TODO: Sometimes the range is buggy and fails to insert the mention.
            if (editor.view.state.mention$.text.length > 1) {
                range.to = range.from + (editor.view.state.mention$.text.length - 1);
            }

            if (overrideSpace) {
                range.to += 1
            }

            // delete the existing text before insertion
            editor
                .chain()
                .focus()
                .deleteRange(range)
                .insertContentAt(range, [
                    {
                        type: 'mention',
                        attrs: props,
                    },
                    {
                        type: 'text',
                        text: ' ',
                    },
                ])
                .run()

            // get reference to `window` object from editor element, to support cross-frame JS usage
            editor.view.dom.ownerDocument.defaultView?.getSelection()?.collapseToEnd()
        },

        render: () => {
            console.log('render');
            let popup;
            let component;

            return {
                onStart: (props) => {
                    popup = tippy('body', {
                        getReferenceClientRect: props.clientRect,
                        content: (() => {
                            component = Alpine.data('filamentCommentsMentions', () => ({
                                add(item) {
                                    props.command({ id: item.id, label: item.name });
                                },
                            }));

                            const container = document.createElement('div');
                            container.setAttribute('x-data', 'filamentCommentsMentions');
                            container.innerHTML = `
                                <template x-for='item in $store.filamentCommentsMentionsFiltered.items' :key='item.id'>
                                    <div class="mention-item" x-text="item.name" @click="add(item)"></div>
                                </template>
                            `;
                            return container;
                        })(),
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom-start',
                        theme: 'light',
                        arrow: false,
                    });
                },
                onUpdate: (props) => {
                    // console.log('props', props);
                    if (!props.clientRect) {
                        return
                    }
                    popup[0].setProps({
                        getReferenceClientRect: props.clientRect,
                    });

                    // console.log('onUpdate', props);
                    // popup.setContent(props.items.map(item => {
                        //     console.log('item', item);
                    //     const div = document.createElement('div');
                    //     div.textContent = item;
                    //     div.classList.add('mention-item');
                    //     div.addEventListener('click', () => {
                        //         props.command({ id: item, label: item });
                    //         popup.hide();
                    //     });
                    //     return div;
                    // }));
                },
                onKeyDown: (props) => {
                    // if (props.event.key === 'ArrowUp') {
                    //     this.upHandler()
                    //     return true
                    // }

                    // if (props.event.key === 'ArrowDown') {
                    //     this.downHandler()
                    //     return true
                    // }

                    // if (props.event.key === 'Enter') {
                    //     this.enterHandler()
                    //     return true
                    // }

                    if (props.event.key === 'Escape') {
                        popup[0].hide();
                        return true;
                    }

                    return false;
                },

                onExit: () => {
                    console.log('onExit');
                    popup[0].hide();
                },

                upHandler() {
                    this.selectedIndex = ((this.selectedIndex + this.items.length) - 1) % this.items.length
                },

                downHandler() {
                    this.selectedIndex = (this.selectedIndex + 1) % this.items.length
                },

                enterHandler() {
                    this.selectItem(this.selectedIndex)
                },

                selectItem(index) {
                    const item = this.items[index]

                    if (item) {
                        this.command({ id: item })
                    }
                },
            };
        },
    }
};

export default renderSuggestionsComponent;
