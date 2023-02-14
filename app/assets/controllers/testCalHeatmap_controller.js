import CalHeatmap from '../cal-heatmap.min'
import Tooltip from '../Tooltip.min'

let cal = new CalHeatmap()

cal.paint({
  date: {
    start: new Date('2020-01-01'),
    min: new Date('2020-09-01'),
    max: new Date('2021-08-31')
  },
  range: 1,
  domain: {
    type: 'year',
    dynamicDimension: true,
    label: {
      position: 'bottom',
      textAlign: 'start',
      text: (timestamp) => {
        return dayjs(timestamp).$y
      }
    },
    subLabel: {
      text: () => {
        return dayjs.weekdays().map(d => d[0].toUpperCase())
      }
    }
  },
  subDomain: {
    type: 'day',
    radius: 2,
  },
  data: {
    source: [
      {date: '2020-01-01EST', value: 5},
      {date: '2020-01-02EST', value: 14},
      {date: '2020-01-03EST', value: 22},
      {date: '2020-01-04EST', value: 25},
      {date: '2020-01-05EST', value: 0},
      {date: '2020-01-06EST', value: 0},
      {date: '2020-01-07EST', value: 0},
      {date: '2020-01-08EST', value: 0},
      {date: '2020-01-09EST', value: 0},
    ],
    x: 'date',
    y: 'value',
    groupY: 'sum'
  },
  scale: {
    as: 'color',
    type: 'linear', //
    scheme: 'Turbo',
    domain: [0, 30],
  }
}, [
  [
    Tooltip,
    {
      enabled: true,
      text: (timestamp, value, dayjsDate) => `${dayjsDate.toString()}, ${value}`,
      placement: 'right',
      modifiers: [
        {
          name: 'offset',
          options: {
            offset: [-15, 5]
          }
        }
      ]
    }
  ]
])