import CalHeatmap from '../cal-heatmap.min'
import Tooltip from '../Tooltip.min'

window.genCalHeatmap2 = function () {
  let elements = document.getElementsByClassName('heatmap')
  for (let element of elements) {
    let start = element.dataset.start
    let end = element.dataset.end
    let entries = JSON.parse(element.dataset.entries)
    
    let cal = new CalHeatmap()
    
    cal.on('click', (event, timestamp, value) => {
    
    })
    
    cal.paint({
      itemSelector: element,
      date: {
        start: new Date(start),
        min: new Date(start),
        max: new Date(end),
      },
      range: 2,
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
        source: entries,
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
          text: (timestamp, value, dayjsDate) => `${dayjsDate.format('DD.MM.YYYY')}`,
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
}

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
