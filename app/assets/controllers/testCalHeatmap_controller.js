import CalHeatmap from '../cal-heatmap.min'
import Tooltip from '../Tooltip.min'

window.genCalHeatmap = function () {
  let cal = new CalHeatmap()
  let calData = JSON.parse(document.getElementById('cal-data').innerText)
  
  cal.on('click', (event, timestamp, value) => {
    let el = document.getElementById('day-sign')
    
    let date = new Date(timestamp)
    
    el.innerText = `${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}`
    let test = window.confirm('Navigate?')
    
    if (test) {
      window.location.assign('/dashboard')
    }
  })
  
  cal.paint({
    date: {
      start: new Date(calData.startDate),
      min: new Date(calData.minDate),
      max: new Date(calData.maxDate),
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
      source: calData.data,
      x: 'date',
      y: 'value',
      groupY: 'sum'
    },
    scale: {
      as: 'color',
      type: 'linear',
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
}
