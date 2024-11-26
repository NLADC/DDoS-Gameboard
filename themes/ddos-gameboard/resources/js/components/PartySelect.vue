<template>
    <div class="party-bubble" v-if="party.sortkey > 0" @click="(event) => submitCheck(party.id, event)">
        <div class="flex">
            <div class="name">
                <input type="checkbox" :checked="partyCheckBool" :value="party.id"
                       :id="partySelectid" style="pointer-events: none">
                <label class="partyLabel" :for="partySelectid">{{ party.name }}</label>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        party: Object,
        csrf: String,
        parties: Object
    },

    computed: {
        partySelectid() {
            return 'partyselect' + this.party.id;
        },

        partyCheckBool() {
            return ((this.parties[this.party.id].show == true) ? true : false);
        }
    },

    methods: {


        toggleCheckbox(id) {
            this.parties[this.party.id].show = !(this.parties[this.party.id].show);

            const checkbox = document.getElementById(id);
            if (checkbox) {
                console.log(this.partyCheckBool);
                checkbox.checked = this.partyCheckBool;
            }
        },

        async submitCheck(id, event) {
            event.preventDefault();
            this.toggleCheckbox('partyselect' + id);

            var elm = document.getElementById('partyselect' + id);
            console.debug('elm.value=' + elm.value + ', checked=' + elm.checked);
            this.parties[elm.value].show = elm.checked;

            var tmp = {
                _token: this.csrf,
                mode: 'setParties',
                show: elm.checked,
                partyId: id
            }

            var path = '/api/setting';
            var method = 'POST';

            // simple sent to server setting - no show to user of errors
            const response = await fetch(path, {
                method: method,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(tmp)
            })
                .then(
                    response => response.json())
                .catch(err => {
                    console.debug('A fatal error has occured! ' + err);
                });
        }
    },


};

</script>
