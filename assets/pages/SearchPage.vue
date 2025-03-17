<script setup>
import { ref, onMounted, watch } from 'vue';
import { searchCard, fetchAllSetCode } from '../services/cardService';

const cards = ref([]);
const loadingCards = ref(false);
const searchQuery = ref('');
const selectedSetCode = ref('');
const setCodes = ref([]);


async function loadCards(query = '') {
    loadingCards.value = true;
    try {
        cards.value = await searchCard(query, { setCode: selectedSetCode.value });
    } catch (error) {
        console.error('Error loading cards:', error);
        cards.value = [];
    } finally {
        loadingCards.value = false;
    }
}

watch(searchQuery, (newQuery) => {
    if (newQuery.length >= 3) {
        loadCards(newQuery);
    } else if (newQuery.length === 0) {
        cards.value = [];
    }
});

watch(selectedSetCode, () => {
    loadCards(searchQuery.value);
});

onMounted(async () => {
    setCodes.value = await fetchAllSetCode();
});
</script>

<template>
   <div>
        <h1>Rechercher une Carte</h1>
        <div class="search-container">
            <input 
                v-model="searchQuery"
                type="text"
                placeholder="Rechercher une carte..."
            />
            <select v-model="selectedSetCode">
                <option value="">Tous les sets</option>
                <option v-for="setCode in setCodes" :key="setCode" :value="setCode">{{ setCode }}</option>
            </select>
        </div>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else-if="cards && cards.length > 0">
            <div class="card" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} - {{ card.uuid }}
                </router-link>
            </div>
        </div>
        <div v-else>
            <p v-if="cards==null">Aucune carte trouvée</p>
            <p v-else>Veuillez entrer au moins 3 characters dans la recherche</p>
        </div>
    </div>
</template>

<style scoped>
.card-list {
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    gap: 10px;
}

.card {
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    width: 100%;
}

.card:hover {
    background-color: #f0f0f0;
}

.search-container {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.search-container input {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.search-container select {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
</style>