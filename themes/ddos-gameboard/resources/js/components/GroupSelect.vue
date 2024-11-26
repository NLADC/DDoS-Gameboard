<template>
    <div class="party-bubble" @click="(event) => submitGroupSelectCheck(group.id, group.name, event)">
        <div class="flex">
            <div class="name"><input type="checkbox" :checked="groupChecked" style="pointer-events: none" :value="group.id"
                                     :id="groupSelectid"> <label :for="groupSelectid">{{ group.name }}</label></div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        group: Object,
        csrf: String,
        groups: Object
    },

    computed: {
        groupSelectid() {
            return 'groupselect' + this.group.id;
        },

        groupChecked() {
            return ((this.groups[this.group.id].show == true) ? true : false);
        }

    },
    methods: {
        async submitGroupSelectCheck(id, name, event) {
            event.preventDefault();
            var elm = document.getElementById('groupselect' + id);
            console.debug('elm.value=' + elm.value + ', checked=' + elm.checked);
            this.groups[elm.value].show = elm.checked;
            this.groups[this.group.id].show = !this.groups[this.group.id].show
            elm.checked = this.groupChecked;
            // very dirty
            var groupelm = document.getElementById('id' + name);
            if (groupelm) {
                groupelm.style.setProperty('display', (elm.checked ? 'block' : 'none'));
            }

        }
    }

};
</script>
