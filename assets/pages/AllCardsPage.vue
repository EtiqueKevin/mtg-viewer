<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { fetchAllCards, fetchAllSetCode } from '../services/cardService';

// Initialize route and router
const route = useRoute();
const router = useRouter();

// Initialize pagination
const currentPage = ref(1);

// Initialize set codes
const setCodes = ref([]);
const selectedSetCode = ref('');

// Initialize cards
const data = ref([]);
const loadingCards = ref(true);

async function loadCards() {
    loadingCards.value = true;
    data.value = await fetchAllCards({ setCode: selectedSetCode.value, page: currentPage.value, limit: 100 });
    loadingCards.value = false;
}

watch(selectedSetCode, () => {
    currentPage.value = 1;
    router.push({ query: { page: currentPage.value, setCode: selectedSetCode.value } });
    loadCards();
});

watch([currentPage], () => {
    router.push({ query: { page: currentPage.value, setCode: selectedSetCode.value } });
    loadCards();
});

onMounted(async () => {
    currentPage.value = parseInt(route.query.page, 10) || 1;
    selectedSetCode.value = route.query.setCode || '';
    setCodes.value = await fetchAllSetCode();
    await loadCards();
});
</script>

<template>
    <div class="title">
        <h1>Toutes les cartes</h1>
        <div class="select-container">
            <label for="set-select">Sélectionner un set</label>
            <select id="set-select" v-model="selectedSetCode">
                <option value="">Tous les sets</option>
                <option v-for="setCode in setCodes" :key="setCode">{{ setCode }}</option>
            </select>
        </div>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <div class="card-result" v-for="card in data.cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
        </div>
    </div>
    <div class="pagination">
        <button type="button" @click="currentPage--" :disabled="currentPage <= 1">Précédent</button>
        <span>{{ currentPage }} / {{ data.totalPages }}</span>
        <button type="button" @click="currentPage++" :disabled="currentPage >= data.totalPages">Suivant</button>
    </div>
</template>

<style scoped>
.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 20px;
}

.pagination button {
    padding: 5px 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    cursor: pointer;
}

.card-list {
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    gap: 10px;
}

.card-result {
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
}

.card-result:hover {
    background-color: #f0f0f0;
}

.title {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

select {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
.select-container {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.select-container label {
    font-size: 0.9em;
    color: #666;
}
</style>
