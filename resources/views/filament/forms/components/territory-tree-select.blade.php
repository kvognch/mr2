<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $statePath = $getStatePath();
        $tree = $getTree();
        $descendants = $getDescendants();
    @endphp

    <div
        x-data="{
            state: $wire.$entangle('{{ $statePath }}').live,
            descendants: @js($descendants),
            expanded: {},
            init() {
                this.state = Array.isArray(this.state)
                    ? [...new Set(this.state.map((id) => Number(id)).filter((id) => !Number.isNaN(id)))]
                    : [];
            },
            isExpanded(id) {
                return !!this.expanded[id];
            },
            toggleExpand(id) {
                this.expanded[id] = !this.expanded[id];
            },
            isChecked(id) {
                return Array.isArray(this.state) && this.state.includes(Number(id));
            },
            toggleChecked(id, checked) {
                const targetId = Number(id);
                const children = this.descendants[targetId] || [];
                const ids = [targetId, ...children.map((child) => Number(child))];

                if (checked) {
                    const merged = [...this.state, ...ids];
                    this.state = [...new Set(merged.map((item) => Number(item)))];

                    return;
                }

                const toRemove = new Set(ids);
                this.state = this.state.filter((item) => !toRemove.has(Number(item)));
            },
        }"
        class="space-y-2"
    >
        @include('filament.forms.components.partials.territory-tree-nodes', ['nodes' => $tree, 'level' => 0])
    </div>
</x-dynamic-component>
