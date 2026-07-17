<template>
    <AppLayout :title="transaction ? 'Edit Point Transaction' : 'Add Point Transaction'">
        <form class="max-w-2xl space-y-5 rounded-md border border-slate-200 bg-white p-5" @submit.prevent="submit">
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium text-slate-700">User</label>
                <!-- i used this custom infinite scroll component for scalability. 
                 We cant just fetch all users at once as it would impact performance -->
                <InfiniteSelect
                    :selectedId="form.user_id"
                    v-model="form.user_id"
                    :endpoint="userEndpoint"
                    option-label="name"
                    option-value="id"
                    placeholder="Search and select user..."
                    :depends-on="null"
                    depends-on-field=""
                    :filters="{}"
                    :initial-limit="10"
                    :lazy-load="false"
                    :disabled="!can.edit_transaction"
                    class="w-full"
                    @select="handleUserSelect"
                    @loaded="handleUsersLoaded"
                />
                <span v-if="form.errors.user_id" class="mt-1 block text-sm text-rose-700">
                    {{ form.errors.user_id }}
                </span>
            </div>

            <SelectInput
                v-model="form.action_type"
                label="Action Type"
                :options="[
                    { value: 'Earn', label: 'Earn' },
                    { value: 'Deduct', label: 'Deduct' },
                ]"
                :error="form.errors.action_type"
            />
            
            <TextInput 
                v-model="form.points" 
                label="Points" 
                type="number" 
                :error="form.errors.points" 
            />
            
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Description</span>
                <textarea 
                    v-model="form.description" 
                    rows="4" 
                    class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-100" 
                />
                <span v-if="form.errors.description" class="mt-1 block text-sm text-rose-700">
                    {{ form.errors.description }}
                </span>
            </label>

            <div class="flex gap-3">
                <PrimaryButton type="submit" :disabled="form.processing || !can.edit_transaction">
                    {{ transaction ? 'Update Transaction' : 'Save Transaction' }}
                </PrimaryButton>
                <Link href="/transactions" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import SelectInput from '../../Components/SelectInput.vue';
import TextInput from '../../Components/TextInput.vue';
import InfiniteSelect from '../../Components/InfiniteScroll/Select.vue';

const page = usePage();
const can = page.props.can;

const props = defineProps({
    transaction: Object,
    users: Array,
    selectedUserId: [Number, String, null],
});

const userEndpoint = '/api/v1/users';

const form = useForm({
    user_id: props.transaction?.user_id || props.selectedUserId || '',
    action_type: props.transaction?.action_type || 'Earn',
    points: props.transaction?.points || 1,
    description: props.transaction?.description || '',
});

 // in case of debugging, you can watch the form data changes
const handleUserSelect = (user) => {
    console.log('Selected user:', user);
};

// in case of debugging, you can watch the form data changes
const handleUsersLoaded = (users) => {
    console.log('Users loaded:', users.length);
};

watch(
    () => form.user_id,
    (newUserId) => {
        console.log('Selected user ID changed:', newUserId);
    }
);

const submit = () => {
    if (props.transaction) {
        form.put(`/transactions/${props.transaction.id}`,{
            onError: (errors) => {
                console.log('Update errors:', errors);
            }
        }
        );
    } else {
        form.post('/transactions');
    }
// }
};

onMounted(() => {
    if (props.selectedUserId) {
        form.user_id = props.selectedUserId;
    }
});
</script>