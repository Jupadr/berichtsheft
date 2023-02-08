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
      {date: '2020-08-28', value: 10},
      {date: '2020-07-20', value: 200},
    ]
  },
  scale: {
    as: 'color',
    type: 'linear',
    scheme: 'PRGn',
    domain: [0, 40]
  }
}, [
  [
    Tooltip,
    {
      enabled: true,
      text: (timestamp, value, dayjsDate) => `${dayjsDate.toString()}`,
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