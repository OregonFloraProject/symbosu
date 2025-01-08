export const RANK_FAMILY = 140;
export const RANK_GENUS = 180;

export const CLID_RARE_ALL = 14948;

export const KEY_NAMES = {
  // description
  plants: 'Plants',
  leaves: 'Leaves',
  inflorescences: 'Inflorescences',
  flowers: 'Flowers',
  fruits: 'Fruits',
  seeds: 'Seeds',

  // context
  family: 'Family',
  status: 'Status',
  ecoregion: 'Ecoregion',
  counties: 'OR Counties',
  habitat: 'Habitat',
  elevation: 'Elevation',
  floweringTime: 'Flowering time',

  // context status sub-keys
  ranking: 'Ranking',
  federal: 'Federal',
  state: 'State',
  heritage: 'ORBIC',

  // survey & manage
  bestSurveyStatus: 'Best survey status',
  bestSurveyTime: 'Best survey time',
  threats: 'Threats',
  management: 'Management',
};

export const SUB_KEY_LIST_ORDERS = {
  status: ['heritage', 'state', 'federal'],
};
